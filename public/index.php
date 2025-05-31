<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Conexión a base de datos InfinityFree
function getDb() {
    $host = 'sql213.infinityfree.com';
    $db   = 'if0_39114660_torneo_futbol';
    $user = 'if0_39114660';
    $pass = 'IhNVZLcwOHbNxd';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new PDOException("Error de conexión: " . $e->getMessage(), (int)$e->getCode());
    }
}

// Función para enviar respuestas JSON
function sendJson(Response $response, $data, int $status = 200): Response {
    $json = json_encode($data);
    $response->getBody()->write($json ?: json_encode(['error' => 'Error al codificar JSON']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
}

// ✅ Ruta raíz para evitar error 502
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("🚀 API Slim funcionando correctamente.");
    return $response;
});

// Ruta de prueba
$app->get('/hello', function (Request $request, Response $response) {
    $response->getBody()->write("¡Hola desde Slim!");
    return $response->withHeader('Content-Type', 'text/plain');
});

// GET: lista de equipos
$app->get('/equipos', function (Request $request, Response $response) {
    $db = getDb();
    $stmt = $db->query("SELECT * FROM equipos");
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return sendJson($response, $equipos);
});

// POST: agregar equipo
$app->post('/equipos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $sql = "INSERT INTO equipos (nombre, departamento) VALUES (:nombre, :departamento)";
    $db = getDb();
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':departamento' => $data['departamento']
    ]);
    return sendJson($response, ['mensaje' => 'Equipo agregado con éxito'], 201);
});

// POST: agregar jugador
$app->post('/jugadores', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $sql = "INSERT INTO jugadores (nombre, posicion, edad, equipo_id) VALUES (:nombre, :posicion, :edad, :equipo_id)";
    $db = getDb();
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':posicion' => $data['posicion'],
        ':edad' => $data['edad'],
        ':equipo_id' => $data['equipo_id']
    ]);
    return sendJson($response, ['mensaje' => 'Jugador agregado con éxito'], 201);
});

// GET: jugador por ID
$app->get('/jugadores/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $db = getDb();
    $stmt = $db->prepare("SELECT * FROM jugadores WHERE id = ?");
    $stmt->execute([$id]);
    $jugador = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($jugador) {
        return sendJson($response, $jugador);
    } else {
        return sendJson($response, ['mensaje' => 'Jugador no encontrado'], 404);
    }
});

$app->run();
