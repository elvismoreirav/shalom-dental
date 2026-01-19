<?php
/**
 * ============================================================================
 * SHALOM DENTAL - Layout Auth (Diseño Premium)
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Shalom Dental') ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        shalom: {
                            primary: '#1E4D3A',    // Verde Corporativo
                            dark: '#163A2C',       // Verde Hover
                            accent: '#A3B7A5',     // Verde Borde
                            gold: '#D6C29A',       // Focus Ring
                            surface: '#F9F8F4',    // Fondo suave
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden bg-shalom-primary">
        
        <div class="absolute top-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full mix-blend-overlay filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-shalom-gold opacity-10 rounded-full mix-blend-overlay filter blur-3xl translate-x-1/2 translate-y-1/2"></div>

        <div class="w-full max-w-[420px] relative z-10">
            <?= $this->yield('content') ?>
            
            <div class="mt-8 text-center space-y-2">
    <p class="text-shalom-accent text-xs">
        &copy; <?= date('Y') ?> Shalom Dental. Todos los derechos reservados.
    </p>
    
    <div class="text-xs text-shalom-accent/60 flex items-center justify-center gap-1">
        <span>Desarrollado por</span>
        <a href="https://tu-agencia.com" target="_blank" class="font-semibold hover:text-white transition-colors flex items-center gap-1 group">
            TuEmpresa
            
            </a>
    </div>
</div>
        </div>
    </div>
</body>
</html>