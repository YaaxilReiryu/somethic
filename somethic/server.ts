import http from 'http';
import { spawn, ChildProcess } from 'child_process';
import path from 'path';

const PHP_PORT = 8088;
const PORT = 3000;

// Start PHP built-in server
console.log(`Starting PHP development server on 127.0.0.1:${PHP_PORT}...`);
const phpProcess: ChildProcess = spawn('php', ['-S', `127.0.0.1:${PHP_PORT}`], {
  stdio: 'inherit',
  cwd: process.cwd(),
});

phpProcess.on('error', (err) => {
  console.error('Failed to start PHP server:', err);
});

phpProcess.on('exit', (code, signal) => {
  console.log(`PHP server exited with code ${code}, signal ${signal}`);
});

process.on('exit', () => {
  if (phpProcess && !phpProcess.killed) phpProcess.kill();
});
process.on('SIGINT', () => {
  if (phpProcess && !phpProcess.killed) phpProcess.kill();
  process.exit();
});
process.on('SIGTERM', () => {
  if (phpProcess && !phpProcess.killed) phpProcess.kill();
  process.exit();
});

// Proxy HTTP requests to PHP
const server = http.createServer((clientReq, clientRes) => {
  let urlPath = clientReq.url || '/';

  // If request is exact '/admin' without trailing slash, redirect to '/admin/'
  if (urlPath === '/admin') {
    clientRes.writeHead(302, { Location: '/admin/' });
    clientRes.end();
    return;
  }

  // If request is root '/' or '/?' rewrite to /index.php
  if (urlPath === '/' || urlPath.startsWith('/?')) {
    const query = urlPath.includes('?') ? urlPath.substring(urlPath.indexOf('?')) : '';
    urlPath = '/index.php' + query;
  }

  // If request is '/admin/' or '/admin/?' rewrite to /admin/index.php
  if (urlPath === '/admin/' || urlPath.startsWith('/admin/?')) {
    const query = urlPath.includes('?') ? urlPath.substring(urlPath.indexOf('?')) : '';
    urlPath = '/admin/index.php' + query;
  }

  // Extract session token from query string for iframe cookie fallback
  let sidParam: string | null = null;
  try {
    const parsed = new URL(urlPath, 'http://127.0.0.1:3000');
    sidParam = parsed.searchParams.get('sid');
  } catch (e) {}

  let cookieHeader = clientReq.headers['cookie'] || '';
  if (sidParam && !cookieHeader.includes('PHPSESSID=')) {
    cookieHeader = cookieHeader ? `${cookieHeader}; PHPSESSID=${sidParam}` : `PHPSESSID=${sidParam}`;
  }

  const options: http.RequestOptions = {
    hostname: '127.0.0.1',
    port: PHP_PORT,
    path: urlPath,
    method: clientReq.method,
    headers: {
      ...clientReq.headers,
      cookie: cookieHeader,
      host: `127.0.0.1:${PHP_PORT}`,
      'x-forwarded-host': clientReq.headers['x-forwarded-host'] || clientReq.headers.host || `localhost:${PORT}`,
      'x-forwarded-proto': clientReq.headers['x-forwarded-proto'] || 'https',
    },
  };

  const phpReq = http.request(options, (phpRes) => {
    const resHeaders = { ...phpRes.headers };

    // Rewrite cookies for seamless cross-origin iframe support (SameSite=None; Secure; Partitioned)
    if (resHeaders['set-cookie']) {
      const rawCookies = Array.isArray(resHeaders['set-cookie'])
        ? resHeaders['set-cookie']
        : [resHeaders['set-cookie']];

      resHeaders['set-cookie'] = rawCookies.map((cookieStr) => {
        let c = cookieStr;
        if (!/;\s*Path=/i.test(c)) {
          c += '; Path=/';
        }
        if (!/;\s*SameSite=/i.test(c)) {
          c += '; SameSite=None';
        } else {
          c = c.replace(/;\s*SameSite=[^;]+/i, '; SameSite=None');
        }
        if (!/;\s*Secure/i.test(c)) {
          c += '; Secure';
        }
        if (!/;\s*Partitioned/i.test(c)) {
          c += '; Partitioned';
        }
        return c;
      });
    }

    // Preserve sid on redirect locations if sid was provided
    if (resHeaders['location'] && sidParam && !resHeaders['location'].includes('sid=')) {
      const loc = resHeaders['location'];
      const sep = loc.includes('?') ? '&' : '?';
      resHeaders['location'] = `${loc}${sep}sid=${encodeURIComponent(sidParam)}`;
    }

    clientRes.writeHead(phpRes.statusCode || 200, resHeaders);
    phpRes.pipe(clientRes, { end: true });
  });

  phpReq.on('error', (err) => {
    console.error('Proxy to PHP error:', err.message);
    clientRes.writeHead(502, { 'Content-Type': 'text/html; charset=utf-8' });
    clientRes.end(`
      <html>
        <body style="font-family: sans-serif; text-align: center; padding: 50px;">
          <h2>SOMETHIC Web Service Initializing...</h2>
          <p>Please wait a few moments while the PHP environment connects to the database.</p>
          <script>setTimeout(() => window.location.reload(), 2000);</script>
        </body>
      </html>
    `);
  });

  clientReq.pipe(phpReq, { end: true });
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`SOMETHIC Retail Application running at http://0.0.0.0:${PORT}`);
});
