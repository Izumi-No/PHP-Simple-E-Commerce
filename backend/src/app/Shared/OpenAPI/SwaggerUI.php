<?php

namespace Izumi\Backend\app\Shared\OpenAPI;

final class SwaggerUI
{
    public static function html(string $specUrl): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Swagger UI</title>
            <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
            <style>
                html { box-sizing: border-box; background: #1b1b1f; }
            </style>
        </head>
        <body>
            <div id="swagger-ui"></div>
            <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
            <script>
                window.onload = function () {
                    window.ui = SwaggerUIBundle({
                        url: '{$specUrl}',
                        dom_id: '#swagger-ui',
                        deepLinking: true,
                    });
                };
            </script>
        </body>
        </html>
        HTML;
    }
}