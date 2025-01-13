<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Input and output file paths
$inputFile = './computer_science_temp.csv';
$outputDirectory = './outputs/';
$outputFile = $outputDirectory . 'candidates_computer_science_temp_output.csv';

// Ensure input file exists
if (!file_exists($inputFile)) {
    die("Input file does not exist.\n");
}

// Create the output directory if it doesn't exist
if (!is_dir($outputDirectory)) {
    if (!mkdir($outputDirectory, 0777, true)) {
        die("Failed to create output directory.\n");
    }
}

// Open the input file for reading
$inputHandle = fopen($inputFile, 'r');
if (!$inputHandle) {
    die("Failed to open input file.\n");
}

// Open the output file for writing
$outputHandle = fopen($outputFile, 'w');
if (!$outputHandle) {
    die("Failed to create or open output file for writing.\n");
}

// Read the header row from the input file
$headers = fgetcsv($inputHandle);
if (!$headers) {
    die("Input file is empty or corrupted.\n");
}

// Define the base output headers
$baseHeaders = ['candidate_id', 'candidate_name', 'mobile', 'email', 'resume'];
$dynamicHeaders = [];

// Read all data into an array for processing
$data = [];
while (($row = fgetcsv($inputHandle)) !== false) {
    $data[] = array_combine($headers, $row);
}

// Group data by candidate ID
$groupedData = [];
foreach ($data as $row) {
    $candidateId = $row['id'];
    if (!isset($groupedData[$candidateId])) {
        $groupedData[$candidateId] = [
            'candidate_id' => $candidateId,
            'candidate_name' => $row['candidate_name'],
            'mobile' => $row['mobile'],
            'email' => $row['email'],
            'resume' => $row['resume'],
            'education' => []
        ];
    }
    $groupedData[$candidateId]['education'][] = [
        'education_id' => $row['education_id'],
        'education_type' => $row['education_type'],
        'specialization' => $row['specialization']
    ];
}

// Generate dynamic headers based on the maximum number of educations
$maxEducationCount = 0;
foreach ($groupedData as $candidate) {
    $maxEducationCount = max($maxEducationCount, count($candidate['education']));
}

for ($i = 1; $i <= $maxEducationCount; $i++) {
    $dynamicHeaders[] = "education_id$i";
    $dynamicHeaders[] = "education_type$i";
    $dynamicHeaders[] = "specialization$i";
}

// Write the headers to the output file
fputcsv($outputHandle, array_merge($baseHeaders, $dynamicHeaders));

// Write the transformed data to the output file
foreach ($groupedData as $candidate) {
    $row = [
        $candidate['candidate_id'],
        $candidate['candidate_name'],
        $candidate['mobile'],
        $candidate['email'],
        $candidate['resume']
    ];
    foreach ($candidate['education'] as $education) {
        $row[] = $education['education_id'];
        $row[] = $education['education_type'];
        $row[] = $education['specialization'];
    }
    // Fill remaining columns with empty values
    $remainingColumns = $maxEducationCount * 3 - count($candidate['education']) * 3;
    $row = array_merge($row, array_fill(0, $remainingColumns, ''));

    fputcsv($outputHandle, $row);
}

// Close the file handles
fclose($inputHandle);
fclose($outputHandle);

echo "Filtered candidates saved to: $outputFile\n";
?>
