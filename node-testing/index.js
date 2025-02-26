const express = require('express');
const multer = require('multer');
const fs = require('fs');
const path = require('path');
const axios = require('axios');
const crypto = require('crypto');
const FormData = require('form-data');

const app = express();
const PORT = 3000;

// Multer Storage with File Extension Preservation
const storage = multer.diskStorage({
    destination: 'uploads/',
    filename: (req, file, cb) => {
        const ext = path.extname(file.originalname); // Get file extension
        cb(null, `${file.fieldname}-${Date.now()}${ext}`);
    }
});
const upload = multer({ storage });

// API Credentials
const username = 'admin';
const password = 'RiskyPassword@98';
const apiUrlPath = '/ISAPI/Intelligent/FDLib/FaceDataRecord?format=json&devIndex=BFEFB066-E74F-954D-A6C4-D62AB3146645';
const apiUrl = `http://13.200.163.13:80${apiUrlPath}`;

// Function to Compute Digest Authentication Header
async function getDigestAuthHeaders(url, method) {
    try {
        // First request to get the `WWW-Authenticate` challenge
        await axios.request({ method, url });
    } catch (error) {
        if (error.response && error.response.status === 401) {
            const authHeader = error.response.headers['www-authenticate'];
            if (!authHeader) throw new Error('No WWW-Authenticate header found');

            const authParams = {};
            authHeader.match(/(\w+)="([^"]+)"/g).forEach(param => {
                const [key, value] = param.split('=');
                authParams[key] = value.replace(/"/g, '');
            });

            // Generate HA1, HA2, and response hashes
            const ha1 = crypto.createHash('md5').update(`${username}:${authParams.realm}:${password}`).digest('hex');
            const ha2 = crypto.createHash('md5').update(`${method}:${apiUrlPath}`).digest('hex');
            const cnonce = crypto.randomBytes(8).toString('hex');
            const nc = '00000001';
            const responseHash = crypto.createHash('md5')
                .update(`${ha1}:${authParams.nonce}:${nc}:${cnonce}:${authParams.qop}:${ha2}`)
                .digest('hex');

            return `Digest username="${username}", realm="${authParams.realm}", nonce="${authParams.nonce}", uri="${apiUrlPath}", response="${responseHash}", qop="auth", nc=${nc}, cnonce="${cnonce}", opaque="${authParams.opaque}", algorithm="MD5"`;
        }
        throw error;
    }
}

// Upload API
app.post('/upload', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ error: 'No file uploaded' });
        }

        console.log('Received file:', req.file.path);

        // Generate Digest Auth Header
        const authHeader = await getDigestAuthHeaders(apiUrl, 'POST');

        // Create Form Data
        const formData = new FormData();
        formData.append('faceinfo', JSON.stringify({
            FaceInfo: { employeeNo: "1574" }
        }), { contentType: 'application/json' });

        formData.append('image', fs.createReadStream(req.file.path));

        // Send request
        const response = await axios.post(apiUrl, formData, {
            headers: {
                ...formData.getHeaders(),
                Authorization: authHeader,
            },
            timeout: 30000
        });

        // Delete file after sending
        fs.unlinkSync(req.file.path);

        res.json({ success: true, response: response.data });

    } catch (error) {
        console.error('Error:', error.response ? error.response.data : error.message);
        res.status(500).json({ error: error.message });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
});
