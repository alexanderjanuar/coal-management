// ai-invoice-processor.js
import { GoogleGenerativeAI } from "@google/generative-ai";
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

class InvoiceAIProcessor {
    constructor() {
        this.genAI = new GoogleGenerativeAI(process.env.GOOGLE_GEMINI_API);
        this.model = this.genAI.getGenerativeModel({ model: "gemini-2.0-flash-exp" });
    }

    async processInvoice(filePath, clientName, monthName) {
        try {
            console.log(`Processing invoice: ${filePath}`);
            
            // Construct the full file path
            const fullPath = this.constructFilePath(filePath, clientName, monthName);
            
            // Check if file exists
            if (!fs.existsSync(fullPath)) {
                throw new Error(`File not found: ${fullPath}`);
            }

            // Read and encode file
            const fileBuffer = fs.readFileSync(fullPath);
            const base64Data = fileBuffer.toString("base64");
            
            // Determine MIME type
            const mimeType = this.getMimeType(fullPath);
            
            // Prepare the prompt for Indonesian tax invoice
            const prompt = `Analisis dokumen faktur pajak Indonesia ini dan ekstrak informasi berikut dalam format JSON yang tepat:

{
    "invoice_number": "nomor faktur pajak lengkap",
    "invoice_date": "tanggal faktur dalam format YYYY-MM-DD",
    "company_name": "nama perusahaan lengkap",
    "npwp": "nomor NPWP lengkap",
    "type": "Faktur Keluaran atau Faktur Masuk",
    "dpp": "nilai DPP dalam angka saja (tanpa titik, koma, atau simbol)",
    "ppn_percentage": "11 atau 12",
    "ppn": "nilai PPN dalam angka saja (tanpa titik, koma, atau simbol)"
}

Instruksi penting:
- Nomor faktur harus dalam format Indonesia (contoh: 010.000-25.12345678)
- Tanggal harus format YYYY-MM-DD
- NPWP harus format Indonesia (contoh: 01.234.567.8-901.000)
- DPP dan PPN hanya angka, tanpa pemisah ribuan
- Type hanya "Faktur Keluaran" atau "Faktur Masuk"
- PPN percentage hanya "11" atau "12"
- Jika ada field yang tidak ditemukan, gunakan nilai default yang masuk akal

Berikan hanya JSON, tanpa penjelasan tambahan.`;

            // Prepare content for Gemini
            const contents = [
                {
                    role: "user",
                    parts: [
                        { text: prompt },
                        {
                            inlineData: {
                                mimeType: mimeType,
                                data: base64Data
                            }
                        }
                    ]
                }
            ];

            // Generate content with Gemini
            const result = await this.model.generateContent(contents);
            const response = await result.response;
            const text = response.text();
            
            console.log("Raw AI Response:", text);
            
            // Parse and validate the response
            const extractedData = this.parseAndValidateResponse(text);
            
            return {
                success: true,
                data: extractedData,
                raw_response: text
            };

        } catch (error) {
            console.error("Error processing invoice:", error);
            return {
                success: false,
                error: error.message,
                data: null
            };
        }
    }

    constructFilePath(uploadedPath, clientName = 'unknown-client', monthName = 'unknown-month') {
        // Handle different path scenarios
        if (path.isAbsolute(uploadedPath)) {
            return uploadedPath;
        }
        
        // For Laravel project structure with script in /resources/scripts/
        // We need to go up 3 levels: resources/scripts -> resources -> project root
        const projectRoot = path.join(__dirname, '../../');
        
        // If it's a relative path from Laravel storage
        if (uploadedPath.startsWith('temp/ai-processing/')) {
            // Laravel storage path structure
            const storagePath = path.join(projectRoot, 'storage/app/public', uploadedPath);
            return storagePath;
        }
        
        // If it's just a filename, construct the full path
        const storagePath = path.join(projectRoot, 'storage/app/public/temp/ai-processing', uploadedPath);
        return storagePath;
    }

    getMimeType(filePath) {
        const extension = path.extname(filePath).toLowerCase();
        
        const mimeTypes = {
            '.pdf': 'application/pdf',
            '.jpg': 'image/jpeg',
            '.jpeg': 'image/jpeg',
            '.png': 'image/png',
            '.webp': 'image/webp'
        };

        return mimeTypes[extension] || 'application/octet-stream';
    }

    parseAndValidateResponse(responseText) {
        try {
            // Extract JSON from response
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (!jsonMatch) {
                throw new Error("No JSON found in response");
            }

            const jsonData = JSON.parse(jsonMatch[0]);
            
            // Validate and clean data
            const cleanedData = {
                invoice_number: this.cleanString(jsonData.invoice_number || ''),
                invoice_date: this.validateDate(jsonData.invoice_date || ''),
                company_name: this.cleanString(jsonData.company_name || ''),
                npwp: this.cleanString(jsonData.npwp || ''),
                type: this.validateInvoiceType(jsonData.type || 'Faktur Keluaran'),
                dpp: this.cleanNumber(jsonData.dpp || '0'),
                ppn_percentage: this.validatePpnPercentage(jsonData.ppn_percentage || '11'),
                ppn: this.cleanNumber(jsonData.ppn || '0')
            };

            // Basic validation
            if (!cleanedData.invoice_number || !cleanedData.company_name) {
                throw new Error("Missing required fields: invoice_number or company_name");
            }

            return cleanedData;

        } catch (error) {
            throw new Error(`Failed to parse AI response: ${error.message}`);
        }
    }

    cleanString(str) {
        return str.toString().trim();
    }

    cleanNumber(num) {
        return num.toString().replace(/[^0-9]/g, '');
    }

    validateDate(dateStr) {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) {
            return new Date().toISOString().split('T')[0]; // Return today if invalid
        }
        return date.toISOString().split('T')[0];
    }

    validateInvoiceType(type) {
        const validTypes = ['Faktur Keluaran', 'Faktur Masuk'];
        return validTypes.includes(type) ? type : 'Faktur Keluaran';
    }

    validatePpnPercentage(percentage) {
        const validPercentages = ['11', '12'];
        return validPercentages.includes(percentage.toString()) ? percentage.toString() : '11';
    }
}

// Main execution function
async function processInvoiceFile(filePath, clientName, monthName) {
    const processor = new InvoiceAIProcessor();
    const result = await processor.processInvoice(filePath, clientName, monthName);
    
    // Output result as JSON for PHP to consume
    console.log(JSON.stringify(result, null, 2));
    return result;
}

// Handle command line arguments
if (process.argv.length >= 3) {
    const filePath = process.argv[2];
    const clientName = process.argv[3] || 'unknown-client';
    const monthName = process.argv[4] || 'unknown-month';
    
    processInvoiceFile(filePath, clientName, monthName)
        .then(result => {
            process.exit(result.success ? 0 : 1);
        })
        .catch(error => {
            console.error(JSON.stringify({
                success: false,
                error: error.message,
                data: null
            }));
            process.exit(1);
        });
}

export { InvoiceAIProcessor, processInvoiceFile };