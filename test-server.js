import phpServer from 'php-server';

const server = await phpServer({
   port: 8002,
   router: './goget.php',
});
console.log(`PHP server running at ${server.url}`);
