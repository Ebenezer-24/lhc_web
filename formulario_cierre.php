<?php
// Bloque de arranque correcto
require_once __DIR__ . '/lib/vendor/autoload.php';
require_once __DIR__ . '/ezcomponents/Base/src/base.php';
ezcBase::addClassRepository( './','./lib/autoloads');
spl_autoload_register(array('ezcBase','autoload'), true, false);
spl_autoload_register(array('erLhcoreClassSystem','autoload'), true, false);
erLhcoreClassSystem::init();

$chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Cierre</title>
    <link rel="stylesheet" type="text/css" href="/design/defaulttheme/vendor/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/design/defaulttheme/css/app.css" />
</head>
<body class="p-3">
    <h4>Resumen de la Asistencia</h4>
    <p>Completa los campos para poder cerrar el chat.</p>

    <div id="status-message" class="d-none mb-2"></div>

    <form id="cierre-form">
        
        <div class="mb-3">
            <label for="motivo_cierre" class="form-label">Motivo de Cierre <span class="text-danger">*</span></label>
            <select id="motivo_cierre" class="form-select" required>
                <option value="">Selecciona un motivo...</option>
                <option value="Solucionado">Solucionado</option>
                <option value="Cerrado por inactividad del cliente">Cerrado por inactividad del cliente</option>
                <option value="Pendiente">Pendiente</option>
            </select>
        </div>

        <div class="row d-none" id="producto_modulo_wrapper">
            <div class="col-md-6 mb-3">
                <label for="producto" class="form-label">Producto <span class="text-danger">*</span></label>
                <select id="producto" class="form-select">
                    <option value="">Selecciona un producto...</option>
                    <option value="Maxirest">Maxirest</option>
                    <option value="Odering">Odering</option>
                </select>
            </div>
            <div class="col-md-6 mb-3" id="modulo_wrapper">
                <label for="modulo" class="form-label">Módulo <span class="text-danger">*</span></label>
                <select id="modulo" class="form-select">
                    </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="motivo_llamado" class="form-label">Motivo del llamado <span class="text-danger">*</span></label>
            <textarea id="motivo_llamado" class="form-control" rows="3" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="resolucion_llamado" class="form-label">Resoluci&oacute;n de llamado <span class="text-danger">*</span></label>
            <textarea id="resolucion_llamado" class="form-control" rows="3" required></textarea>
        </div>

    </form>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" id="guardar-y-cerrar" data-chat-id="<?php echo $chatId; ?>">Guardar y Cerrar Chat</button>
    </div>

    <script src="/design/defaulttheme/vendor/jquery/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        
        // --- LÓGICA DEL BOTÓN DE GUARDAR (ACTUALIZADA) ---
        $('#guardar-y-cerrar').on('click', function() {
            // Recolecta todos los datos del formulario
            const motivoCierre = $('#motivo_cierre').val();
            const motivoLlamado = $('#motivo_llamado').val();
            const resolucionLlamado = $('#resolucion_llamado').val();
            
            // Aunque están ocultos, los enviamos vacíos para no modificar el backend
            const producto = $('#producto').val();
            const modulo = $('#modulo').val();
            
            const chatId = $(this).data('chat-id');
            const statusDiv = $('#status-message');
            const button = $(this);
            const parentLHC = window.parent;

            // Validación de seguridad y campos obligatorios (actualizada)
            if (!parentLHC || !parentLHC.confLH || !parentLHC.confLH.csrf_token) {
                statusDiv.removeClass('d-none').addClass('alert alert-danger').text('Error Crítico: No se pudo obtener el token de seguridad (CSRF).');
                return;
            }
            if (!motivoCierre || !motivoLlamado.trim() || !resolucionLlamado.trim()) {
                statusDiv.removeClass('d-none').addClass('alert alert-danger').text('Error: Debes completar todos los campos obligatorios (*).');
                return;
            }

            const csrfToken = parentLHC.confLH.csrf_token;
            button.prop('disabled', true).text('Guardando...');
            statusDiv.removeClass('d-none alert-danger').addClass('alert alert-info').text('Guardando resumen...');

            // Envía todos los datos al script de guardado
            $.post('guardar_cierre.php?csfr=' + csrfToken, {
                chat_id: chatId,
                motivo_cierre: motivoCierre,
                motivo_llamado: motivoLlamado,
                resolucion_llamado: resolucionLlamado,
                producto: producto, // Se envía vacío
                modulo: modulo      // Se envía vacío
            }).done(function(response) {
                if (response && response.error === false) {
                    statusDiv.removeClass('alert-info').addClass('alert alert-success').text(response.message);
                    
                    setTimeout(function() {
                        parentLHC.lhinst.syncadmincall();
                        parentLHC.lhinst.closeActiveChatDialog(chatId, parentLHC.$('#tabs'), true);
                        parentLHC.$('#myModal').modal('hide');
                    }, 500);
                } else {
                    statusDiv.removeClass('d-none alert-info').addClass('alert alert-danger').text('Error: ' + (response.message || 'Hubo un error inesperado.'));
                    button.prop('disabled', false).text('Guardar y Cerrar Chat');
                }
            }).fail(function(xhr) {
                const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo guardar el resumen.';
                statusDiv.removeClass('d-none alert-info').addClass('alert alert-danger').text('Error: ' + errorMsg);
                button.prop('disabled', false).text('Guardar y Cerrar Chat');
            });
        });
    });
    </script>
</body>
</html>
