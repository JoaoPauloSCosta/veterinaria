<?php
require_once 'config.php';

echo "<h2>🔍 Debug do Erro 404 - Notificações</h2>";

// Informações da requisição
echo "<h3>📋 Informações da Requisição</h3>";
echo "<ul>";
echo "<li><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
echo "<li><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</li>";
echo "<li><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</li>";
echo "<li><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</li>";
echo "<li><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</li>";
echo "<li><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "</li>";
echo "</ul>";

// Configurações da aplicação
echo "<h3>⚙️ Configurações da Aplicação</h3>";
echo "<ul>";
echo "<li><strong>APP_URL:</strong> " . (defined('APP_URL') ? APP_URL : 'N/A') . "</li>";
echo "<li><strong>Base Path:</strong> " . rtrim(parse_url(APP_URL, PHP_URL_PATH), '/') . "</li>";
echo "</ul>";

// Simular o processamento do router
echo "<h3>🔄 Simulação do Router</h3>";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');
$path = '/' . ltrim(str_replace($basePath, '', $uri), '/');

echo "<ul>";
echo "<li><strong>URI Original:</strong> " . $uri . "</li>";
echo "<li><strong>Base Path:</strong> " . $basePath . "</li>";
echo "<li><strong>Path Processado:</strong> " . $path . "</li>";
echo "</ul>";

// Verificar se a rota seria encontrada
if ($path === '/notifications/unread') {
    echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Rota seria encontrada no router!</strong>";
    echo "</div>";
} else {
    echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px;'>";
    echo "<strong>❌ Rota NÃO seria encontrada no router!</strong>";
    echo "</div>";
}

?>

<h3>🧪 Teste de Requisição AJAX</h3>
<button onclick="testNotificationRoute()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">
    Testar Rota via AJAX
</button>
<div id="test-result" style="margin-top: 10px;"></div>

<script>
function testNotificationRoute() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div style="background: #fff3cd; padding: 10px; border-radius: 5px;">⏳ Testando...</div>';
    
    // Testar com diferentes URLs
    const urls = [
        '/notifications/unread',
        'notifications/unread',
        './notifications/unread',
        window.location.origin + '/notifications/unread'
    ];
    
    let results = '<h4>Resultados dos Testes:</h4>';
    let testCount = 0;
    
    urls.forEach((url, index) => {
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            testCount++;
            results += `<div style="margin: 10px 0; padding: 10px; border-radius: 5px; background: ${response.ok ? '#e8f5e8' : '#ffe8e8'};">`;
            results += `<strong>URL ${index + 1}:</strong> ${url}<br>`;
            results += `<strong>Status:</strong> ${response.status} ${response.statusText}<br>`;
            results += `<strong>Content-Type:</strong> ${response.headers.get('content-type') || 'N/A'}`;
            results += `</div>`;
            
            if (testCount === urls.length) {
                resultDiv.innerHTML = results;
            }
        })
        .catch(error => {
            testCount++;
            results += `<div style="margin: 10px 0; padding: 10px; border-radius: 5px; background: #ffe8e8;">`;
            results += `<strong>URL ${index + 1}:</strong> ${url}<br>`;
            results += `<strong>Erro:</strong> ${error.message}`;
            results += `</div>`;
            
            if (testCount === urls.length) {
                resultDiv.innerHTML = results;
            }
        });
    });
}
</script>