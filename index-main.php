<?php

// Function to parse CSV and filter candidates with specified educational qualifications
function filterCandidatesWithDegrees($inputFile, $outputFile)
{
  // Open the input CSV file
  $inputHandle = fopen($inputFile, 'r');

  // Prepare the output file
  $outputHandle = fopen($outputFile, 'w');

  // Write headers to the output CSV file
  fputcsv($outputHandle, ['seeker_id', 'name', 'mobile', 'email', 'resume', 'education_bachelor', 'education_master', 'education_doctorate']);

  // Initialize a storage for seekers
  $seekers = [];

  // Read the first line to skip headers, adjust according to your file's structure
  $headers = fgetcsv($inputHandle);

  // Process each row of the CSV file
  while (($data = fgetcsv($inputHandle)) !== FALSE) {

    // Extract relevant data from the row
    // print_r($data);
    // die();

    $seekerId = $data[0];  // Assuming seeker ID is in the first column
    $educationId = strtolower($data[5]);  // Assuming education type is in the fifht column
    $educationType = strtolower($data[6]);  // Assuming education type is in the fourth column
    $specialization = strtolower($data[7]); // Assuming specialization is in the fifth column

    // Initialize array for the seeker if not already present
    if (!isset($seekers[$seekerId])) {
      $seekers[$seekerId] = [
        'name' => $data[1], // Assuming name is in the second column
        'mobile' => $data[2], // Assuming name is in the third column
        'email' => $data[3], // Assuming email is in the 4 column
        'resume' => $data[4], // Assuming email is in the 5 column
        'bachelor' => null,
        'master' => null,
        'doctorate' => null,
      ];
    }

    // Check and store relevant education details
    if (
      ($educationId == 3) &&
      (strpos($specialization, 'computer science') !== false
        || strpos($specialization, 'computer engineering') !== false
        || strpos($specialization, 'software engineering') !== false ||
        strpos($specialization, 'engg') !== false ||
        strpos($specialization, 'cse') !== false ||
        strpos($specialization, 'c.s.e') !== false||
        strpos($specialization, 'cs&e') !== false
      )

    ) {
      $seekers[$seekerId]['bachelor'] = $data[6] . ' (' . $data[7] . ')';
    } elseif (
      ($educationId == 4) &&
      (strpos($specialization, 'computer science') !== false
        || strpos($specialization, 'computer engineering') !== false
        || strpos($specialization, 'software engineering') !== false ||
        strpos($specialization, 'engg') !== false ||
        strpos($specialization, 'cse') !== false ||
        strpos($specialization, 'c.s.e') !== false||
        strpos($specialization, 'cs&e') !== false

      )
    ) {
      $seekers[$seekerId]['master'] = $data[6] . ' (' . $data[7] . ')';
    } elseif (
      ($educationId == 5) &&
      (strpos($specialization, 'computer science') !== false
        || strpos($specialization, 'computer engineering') !== false
        || strpos($specialization, 'software engineering') !== false ||
        strpos($specialization, 'engg') !== false ||
        strpos($specialization, 'cse') !== false ||
        strpos($specialization, 'c.s.e') !== false||
        strpos($specialization, 'cs&e') !== false
      )
    ) {
      $seekers[$seekerId]['doctorate'] = $data[6] . ' (' . $data[7] . ')';
    }
  }

  // Close the input file
  fclose($inputHandle);

  // Filter seekers with all required degrees
  foreach ($seekers as $seekerId => $details) {
    if ($details['bachelor'] && $details['master'] && $details['doctorate']) {
      fputcsv($outputHandle, [
        $seekerId,
        $details['name'],
        $details['mobile'],
        $details['email'],
        $details['resume'],
        $details['bachelor'],
        $details['master'],
        $details['doctorate']
      ]);
    }
  }

  // Close the output file
  fclose($outputHandle);

  echo "Filtered candidates have been successfully written to $outputFile\n";
}

// Define input and output file paths
$inputFile = './computer_science_main.csv'; // Your input file
$outputDirectory = './outputs/';
$outputFile = $outputDirectory . 'filtered_candidates_computer_science_main.csv';

// Execute the function
filterCandidatesWithDegrees($inputFile, $outputFile);
