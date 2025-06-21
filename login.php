<?php
// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize error message (e.g., from a previous login attempt)
$errorMessage = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
// Clear the error message from session after displaying it
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect-V - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'bounce-subtle': 'bounceSubtle 0.6s ease-out',
                        'pulse-slow': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    backdropBlur: {
                        'xs': '2px',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-50/20 to-purple-50/20 dark:from-blue-900/20 dark:to-purple-900/20">
    <div class="p-8 max-w-md w-full mx-4 animate-fade-in">
        <div class="flex items-center justify-center mb-6" style="flex-direction: column;">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mb-3">
                <i class="fas fa-comments text-white text-lg"></i>
            </div>
            <h1 class="ml-3 text-2xl font-bold">Connect-V</h1>
        </div>
        <h2 class="text-xl font-semibold text-center mb-6">Entrar</h2>
        <form id="loginForm" class="space-y-4" method="POST" action="php/login.php">
            <div class="relative">
                <input type="email" id="email" name="email" placeholder="Digite seu email" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-white transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-envelope absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Digite sua senha" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-white transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-lock absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <?php if (!empty($errorMessage)): ?>
                <div id="errorMessage" class="text-red-500 text-sm animate-fade-in"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php else: ?>
                <div id="errorMessage" class="text-red-500 text-sm hidden animate-fade-in"></div>
            <?php endif; ?>
            <button type="submit" id="loginButton" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-3 rounded-2xl transition-all duration-300 floating-action animate-slide-up">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>
        <p class="text-center mt-4 text-sm text-white-600 dark:text-white-400">
            Não tem uma conta? <a href="register.php" class="text-blue-500 hover:text-blue-600 transition-colors">Registe-se</a>
        </p>
    </div>

    <script>

        // Validação e envio do formulário
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorMessage = document.getElementById('errorMessage');
            const loginButton = document.getElementById('loginButton');
            errorMessage.classList.add('hidden');

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Validações no frontend
            if (!email || !password) {
                errorMessage.textContent = 'Email e senha são obrigatórios';
                errorMessage.classList.remove('hidden');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessage.textContent = 'Email inválido';
                errorMessage.classList.remove('hidden');
                return;
            }

            // Mostrar loading
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';

            // Enviar para o backend
            try {
                const response = await fetch('php/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ email, password }),
                    credentials: 'include'
                });
                console.log('Status da resposta:', response.status);
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
                }
                const result = await response.json();
                console.log('Resposta de login.php:', result);
                if (result.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = result.message;
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erro na requisição de login:', error);
                errorMessage.textContent = 'Erro ao conectar com o servidor: ' + error.message;
                errorMessage.classList.remove('hidden');
            } finally {
                loginButton.disabled = false;
                loginButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Entrar';
            }
        });

        document.addEventListener('DOMContentLoaded', loadTheme);
    </script>
</body>
</html>