<?php

ini_set('display_errors', 0); 
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/lib/vendor/autoload.php';
require_once __DIR__ . '/ezcomponents/Base/src/base.php';
ezcBase::addClassRepository( './','./lib/autoloads');
spl_autoload_register(array('ezcBase','autoload'), true, false);
spl_autoload_register(array('erLhcoreClassSystem','autoload'), true, false);

try {
    $settings = include('settings/settings.ini.php');
    $db = ezcDbFactory::create( 'mysql://' .
        $settings['settings']['db']['user'] . ':' .
        $settings['settings']['db']['password'] . '@' .
        $settings['settings']['db']['host'] . ':' .
        $settings['settings']['db']['port'] . '/' .
        $settings['settings']['db']['database']
    );
    ezcDbInstance::set($db);
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}
erLhcoreClassSystem::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['csfr']) || erLhcoreClassUser::instance()->getCSFRToken() !== $_GET['csfr']) {
    ob_clean();
    http_response_code(403);
    echo json_encode(['error' => true, 'message' => 'Petición inválida.']);
    exit;
}

header('Content-Type: application/json');

try {
    
    $chatId = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
    
    // ======== SECCIÓN DE CAMBIOS ========

    // 1. AÑADIMOS LA LECTURA DEL MOTIVO DE CIERRE
    $motivoCierre = isset($_POST['motivo_cierre']) ? trim($_POST['motivo_cierre']) : '';

    $motivoLlamado = isset($_POST['motivo_llamado']) ? trim($_POST['motivo_llamado']) : '';
    $resolucionLlamado = isset($_POST['resolucion_llamado']) ? trim($_POST['resolucion_llamado']) : '';
    
    // 2. FORZAMOS A QUE PRODUCTO Y MÓDULO SEAN NULL
    $producto = null;
    $modulo = null;
    
    // ====================================
    
    $chat = erLhcoreClassModelChat::fetch($chatId);
    if (!($chat instanceof erLhcoreClassModelChat && erLhcoreClassChat::hasAccessToRead($chat))) {
        throw new Exception("Chat no encontrado o sin permisos.");
    }
    
    $db = ezcDbInstance::get();
    $q = $db->createUpdateQuery();
    $q->update( 'lh_chat' )
      
      // 3. AÑADIMOS EL CAMPO A LA CONSULTA SQL
      ->set( 'motivo_cierre', $q->bindValue($motivoCierre) )

      ->set( 'motivo_del_llamado', $q->bindValue($motivoLlamado) )
      ->set( 'resolucion_del_llamado', $q->bindValue($resolucionLlamado) )
      ->set( 'producto_seleccionado', $q->bindValue($producto) )
      ->set( 'modulo_seleccionado', $q->bindValue($modulo) )
      
      ->where( $q->expr->eq( 'id', $q->bindValue($chatId) ) );
    $stmt = $q->prepare();
    $stmt->execute();
    
    ob_clean();
    echo json_encode(['error' => false, 'message' => 'Resumen guardado exitosamente.']);
    exit;

} catch (Exception $e) {
    
    ob_clean();
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
    exit;
}
?>