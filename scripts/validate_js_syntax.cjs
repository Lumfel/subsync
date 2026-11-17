const fs = require('fs');
const path = require('path');
const vm = require('vm');

let hadError = false;

function checkJsCode(code, name, startLine = 1) {
  // Strip Blade echo and directive expressions
  const sanitized = code
    .replace(/@json\([\s\S]*?\)/g, '""')
    .replace(/@js\([\s\S]*?\)/g, '""')
    .replace(/\{\{\s*[\s\S]*?\s*\}\}/g, '"blade_echo"')
    .replace(/\{!!\s*[\s\S]*?\s*!!\}/g, '"blade_raw"');

  try {
    new vm.Script(sanitized, { filename: name, lineOffset: startLine - 1 });
  } catch (err) {
    hadError = true;
    console.error(`SYNTAX ERROR in ${name} (around line ${startLine}): ${err.message}`);
  }
}

function checkFile(relPath) {
  const fullPath = path.resolve(__dirname, '..', relPath);
  if (!fs.existsSync(fullPath)) return;
  const content = fs.readFileSync(fullPath, 'utf8');

  if (relPath.endsWith('.js')) {
    checkJsCode(content, relPath, 1);
    return;
  }

  // Parse HTML/Blade <script> blocks (excluding external src-only scripts)
  const regex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
  let match;
  let blockIdx = 0;
  while ((match = regex.exec(content)) !== null) {
    blockIdx++;
    const fullTag = match[0];
    const scriptBody = match[1];

    // If it's a src-only script with no body, skip
    if (!scriptBody.trim()) continue;

    // Calculate line number in file
    const startLine = content.substring(0, match.index).split('\n').length;
    checkJsCode(scriptBody, `${relPath} <script #${blockIdx}>`, startLine);
  }
}

const targets = [
  'resources/views/layouts/admin-dashboard.blade.php',
  'resources/views/layouts/officer-portal.blade.php',
  'resources/views/layouts/residents.blade.php',
  'public/js/admin-dashboard.js',
  'public/js/officer-portal.js',
  'public/js/residents.js',
  'public/js/ui.js'
];

targets.forEach(checkFile);

if (hadError) {
  console.error('JS_SYNTAX_CHECK_FAILED');
  process.exit(1);
} else {
  console.log('ALL_JS_SYNTAX_VALID');
}
