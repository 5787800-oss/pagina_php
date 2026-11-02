<?php

// Obtener la API Key desde las variables de entorno del servidor
$brevoApiKey = getenv("BREVO_API_KEY");

define("NOMBRE_RESTAURANTE", "Restaurante Sabor Local");
define("CORREO_REMITENTE", "adrianmonroy4023@gmail.com");

/**
 * Genera la plantilla HTML del ticket de compra.
 */
function construirHtmlFactura(string $nombreCliente, string $itemMenu): string {
    date_default_timezone_set('America/El_Salvador'); // Ajusta tu zona horaria si es necesario
    $fechaActual = date("d/m/Y H:i");

    return "
    <html>
      <body style='margin:0; padding:0; background-color:#f4f4f7; font-family: Arial, Helvetica, sans-serif;'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f4f7; padding:30px 0;'>
          <tr>
            <td align='center'>
              <table role='presentation' width='480' cellpadding='0' cellspacing='0' style='background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08);'>
                <tr>
                  <td style='background-color:#2f6f4e; padding:24px; text-align:center;'>
                    <h1 style='color:#ffffff; margin:0; font-size:22px;'>" . NOMBRE_RESTAURANTE . "</h1>
                    <p style='color:#dcefe3; margin:4px 0 0; font-size:13px;'>Confirmación de pedido</p>
                  </td>
                </tr>
                <tr>
                  <td style='padding:28px 32px;'>
                    <p style='font-size:15px; color:#333333; margin:0 0 12px;'>
                      ¡Hola, <strong>" . htmlspecialchars($nombreCliente) . "</strong>!
                    </p>
                    <p style='font-size:14px; color:#555555; margin:0 0 20px;'>
                      Gracias por tu compra. Hemos recibido tu pedido correctamente.
                      Aquí tienes el detalle de tu recibo:
                    </p>

                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e5e5e5; border-radius:6px; margin-bottom:20px;'>
                      <tr style='background-color:#f9f9f9;'>
                        <td style='padding:10px 14px; font-size:13px; color:#888888;'>Ítem</td>
                        <td style='padding:10px 14px; font-size:13px; color:#888888; text-align:right;'>Fecha</td>
                      </tr>
                      <tr>
                        <td style='padding:12px 14px; font-size:14px; color:#333333; border-top:1px solid #eeeeee;'>
                          " . htmlspecialchars($itemMenu) . "
                        </td>
                        <td style='padding:12px 14px; font-size:14px; color:#333333; border-top:1px solid #eeeeee; text-align:right;'>
                          {$fechaActual}
                        </td>
                      </tr>
                    </table>

                    <p style='font-size:13px; color:#999999; margin:0 0 4px;'>
                      Si tienes alguna pregunta sobre tu pedido, simplemente responde a este correo.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>";
}

/**
 * Envíar correo de confirmación consumiendo la API de Brevo.
 */
function enviarCorreoConfirmacion(string $correoCliente, string $nombreCliente, string $itemMenu): bool {
    global $brevoApiKey;

    if (!$brevoApiKey) {
        error_log("Advertencia: BREVO_API_KEY no está configurada.");
        return false;
    }

    $url = "https://api.brevo.com/v3/smtp/email";

    $payload = [
        "sender" => [
            "name"  => NOMBRE_RESTAURANTE,
            "email" => CORREO_REMITENTE
        ],
        "to" => [
            [
                "email" => $correoCliente,
                "name"  => $nombreCliente
            ]
        ],
        "subject" => "✅ Confirmación de tu pedido en " . NOMBRE_RESTAURANTE,
        "htmlContent" => construirHtmlFactura($nombreCliente, $itemMenu)
    ];

    // Inicializar cURL para hacer la petición HTTP POST
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: " . $brevoApiKey,
        "content-type: application/json"
    ]);

    $respuesta = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Error de conexión con Brevo: " . $curlError);
        return false;
    }

    if ($statusCode === 201) {
        echo "Correo enviado exitosamente a través de Brevo.\n";
        return true;
    } else {
        error_log("Error de Brevo: {$statusCode} - {$respuesta}");
        return false;
    }
}

// Ejemplo de uso:
// enviarCorreoConfirmacion("cliente@ejemplo.com", "Carlos López", "Hamburguesa Especial");
?><?php

// Obtener la API Key desde las variables de entorno del servidor
$brevoApiKey = getenv("BREVO_API_KEY");

define("NOMBRE_RESTAURANTE", "Restaurante Sabor Local");
define("CORREO_REMITENTE", "adrianmonroy4023@gmail.com");

/**
 * Genera la plantilla HTML del ticket de compra.
 */
function construirHtmlFactura(string $nombreCliente, string $itemMenu): string {
    date_default_timezone_set('America/El_Salvador'); // Ajusta tu zona horaria si es necesario
    $fechaActual = date("d/m/Y H:i");

    return "
    <html>
      <body style='margin:0; padding:0; background-color:#f4f4f7; font-family: Arial, Helvetica, sans-serif;'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f4f7; padding:30px 0;'>
          <tr>
            <td align='center'>
              <table role='presentation' width='480' cellpadding='0' cellspacing='0' style='background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.08);'>
                <tr>
                  <td style='background-color:#2f6f4e; padding:24px; text-align:center;'>
                    <h1 style='color:#ffffff; margin:0; font-size:22px;'>" . NOMBRE_RESTAURANTE . "</h1>
                    <p style='color:#dcefe3; margin:4px 0 0; font-size:13px;'>Confirmación de pedido</p>
                  </td>
                </tr>
                <tr>
                  <td style='padding:28px 32px;'>
                    <p style='font-size:15px; color:#333333; margin:0 0 12px;'>
                      ¡Hola, <strong>" . htmlspecialchars($nombreCliente) . "</strong>!
                    </p>
                    <p style='font-size:14px; color:#555555; margin:0 0 20px;'>
                      Gracias por tu compra. Hemos recibido tu pedido correctamente.
                      Aquí tienes el detalle de tu recibo:
                    </p>

                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e5e5e5; border-radius:6px; margin-bottom:20px;'>
                      <tr style='background-color:#f9f9f9;'>
                        <td style='padding:10px 14px; font-size:13px; color:#888888;'>Ítem</td>
                        <td style='padding:10px 14px; font-size:13px; color:#888888; text-align:right;'>Fecha</td>
                      </tr>
                      <tr>
                        <td style='padding:12px 14px; font-size:14px; color:#333333; border-top:1px solid #eeeeee;'>
                          " . htmlspecialchars($itemMenu) . "
                        </td>
                        <td style='padding:12px 14px; font-size:14px; color:#333333; border-top:1px solid #eeeeee; text-align:right;'>
                          {$fechaActual}
                        </td>
                      </tr>
                    </table>

                    <p style='font-size:13px; color:#999999; margin:0 0 4px;'>
                      Si tienes alguna pregunta sobre tu pedido, simplemente responde a este correo.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>";
}

/**
 * Envíar correo de confirmación consumiendo la API de Brevo.
 */
function enviarCorreoConfirmacion(string $correoCliente, string $nombreCliente, string $itemMenu): bool {
    global $brevoApiKey;

    if (!$brevoApiKey) {
        error_log("Advertencia: BREVO_API_KEY no está configurada.");
        return false;
    }

    $url = "https://api.brevo.com/v3/smtp/email";

    $payload = [
        "sender" => [
            "name"  => NOMBRE_RESTAURANTE,
            "email" => CORREO_REMITENTE
        ],
        "to" => [
            [
                "email" => $correoCliente,
                "name"  => $nombreCliente
            ]
        ],
        "subject" => "✅ Confirmación de tu pedido en " . NOMBRE_RESTAURANTE,
        "htmlContent" => construirHtmlFactura($nombreCliente, $itemMenu)
    ];

    // Inicializar cURL para hacer la petición HTTP POST
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: " . $brevoApiKey,
        "content-type: application/json"
    ]);

    $respuesta = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Error de conexión con Brevo: " . $curlError);
        return false;
    }

    if ($statusCode === 201) {
        echo "Correo enviado exitosamente a través de Brevo.\n";
        return true;
    } else {
        error_log("Error de Brevo: {$statusCode} - {$respuesta}");
        return false;
    }
}

// Ejemplo de uso:
// enviarCorreoConfirmacion("cliente@ejemplo.com", "Carlos López", "Hamburguesa Especial");
?>