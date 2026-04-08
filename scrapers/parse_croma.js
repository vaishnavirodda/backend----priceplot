const fs = require('fs');
const path = require('path');

const filePath = 'C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/croma_data.json';
let content = fs.readFileSync(filePath, 'utf8');

// The content is a JS object string, not necessarily strict JSON
// Node can parse it if we wrap it in a function or eval (CAREFULLY!)
// But wait, the file we extracted was: {"brandReducer":...}
// Let's try to parse it as JSON first, but handle undefined

try {
    // Replace undefined with null in a safer way
    // For a raw JS object, we could use eval(`(${content})`) but it's risky
    // However, in a controlled environment like this, it might be the only way if it's not valid JSON
    const data = eval(`(${content})`);
    
    const results = [];
    function findKey(obj) {
        if (!obj || typeof obj !== 'object') return;
        if (Array.isArray(obj)) {
            obj.forEach(findKey);
            return;
        }
        for (const key in obj) {
            if (key === 'products' && Array.isArray(obj[key])) {
                results.push(...obj[key]);
            } else {
                findKey(obj[key]);
            }
        }
    }
    
    findKey(data);
    
    console.log(JSON.stringify(results, null, 2));
} catch (e) {
    console.error("Error parsing JS object:", e.message);
}
