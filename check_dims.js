const fs = require('fs');
const path = require('path');

function getPNGDimensions(filePath) {
    const buffer = fs.readFileSync(filePath);
    if (buffer.toString('ascii', 1, 4) !== 'PNG') {
        throw new Error('Not a PNG file');
    }
    const width = buffer.readUInt32BE(16);
    const height = buffer.readUInt32BE(20);
    return { width, height };
}

try {
    const filePath = 'C:/Users/rene/.gemini/antigravity/brain/ea6d7adb-6482-4087-9fb7-8ac9175b8614/movieshelf_feature_graphic_1775487773816.png';
    const dims = getPNGDimensions(filePath);
    console.log(`Dimensions: ${dims.width}x${dims.height}`);
} catch (e) {
    console.error(e.message);
}
