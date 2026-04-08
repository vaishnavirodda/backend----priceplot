const fs = require('fs');

const htmlPath = 'C:/Users/HP/.gemini/antigravity/brain/969997ab-ffaa-47bc-b7fb-338631636d0f/debug_search_Croma.html';
if (!fs.existsSync(htmlPath)) {
    console.error("File not found:", htmlPath);
    process.exit(1);
}
const html = fs.readFileSync(htmlPath, 'utf8');

// Be flexible with the marker
const markers = ['window.__INITIAL_DATA__=', 'window.__INITIAL_DATA__ ='];
let start = -1;
let usedMarker = '';

for (const m of markers) {
    start = html.indexOf(m);
    if (start !== -1) {
        usedMarker = m;
        break;
    }
}

if (start !== -1) {
    const fromStart = html.substring(start + usedMarker.length);
    // Find the next </script> tag
    const scriptEnd = fromStart.indexOf('</script>');
    if (scriptEnd !== -1) {
        let jsonStr = fromStart.substring(0, scriptEnd).trim();
        // Remove trailing semicolon if present
        if (jsonStr.endsWith(';')) {
            jsonStr = jsonStr.slice(0, -1);
        }
        
        try {
            // Use eval to handle JS objects that aren't strict JSON (undefined, NaN etc)
            const data = eval(`(${jsonStr})`);
            const products = [];
            
            function findKey(obj) {
                if (!obj || typeof obj !== 'object') return;
                if (Array.isArray(obj)) {
                    obj.forEach(p => {
                        if (p && p.name && p.price) {
                            products.push(p);
                        } else {
                            findKey(p);
                        }
                    });
                    return;
                }
                for (const key in obj) {
                    if (key === 'products' && Array.isArray(obj[key])) {
                        products.push(...obj[key]);
                    } else if (key === 'productResults' && Array.isArray(obj[key])) {
                        products.push(...obj[key]);
                    } else {
                        findKey(obj[key]);
                    }
                }
            }
            findKey(data);
            
            const simplified = products.map(p => ({
                name: p.name,
                price: p.price ? p.price.value : (p.mrp ? p.mrp.value : 0),
                formattedPrice: p.price ? p.price.formattedValue : ''
            })).filter(p => p.name && p.price > 0);
            
            console.log(JSON.stringify(simplified, null, 2));
        } catch (e) {
            console.error("Eval error:", e.message);
        }
    } else {
        console.error("Could not find </script> tag");
    }
} else {
    console.error("Could not find INITIAL_DATA marker");
}
