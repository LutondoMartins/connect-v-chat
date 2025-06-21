<?php
// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize error message (e.g., from a previous registration attempt)
$errorMessage = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
// Clear the error message from session after displaying it
unset($_SESSION['register_error']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect-V - Registro</title>
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
        <h2 class="text-xl font-semibold text-center mb-6">Registar</h2>
        <form id="registerForm" class="space-y-4" method="POST" action="php/register.php" enctype="multipart/form-data">
            <div class="relative">
                <input type="text" id="username" name="username" placeholder="Digite seu nome de usuário" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-user absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <div class="relative">
                <input type="email" id="email" name="email" placeholder="Digite seu email" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-envelope absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Digite sua senha" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-lock absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <div class="relative">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme sua senha" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-500 animate-slide-up">
                <i class="fas fa-lock absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <div class="relative">
                <label for="profile_pic" class="block text-sm font-medium text-gray-400 mb-1">Foto de Perfil (opcional)</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png" 
                       class="w-full px-4 py-3 bg-gray-50/50 dark:bg-gray-700/50 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 animate-slide-up">
                <i class="fas fa-image absolute left-4 top-10 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
            </div>
            <?php if (!empty($errorMessage)): ?>
                <div id="errorMessage" class="text-red-500 text-sm animate-fade-in"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php else: ?>
                <div id="errorMessage" class="text-red-500 text-sm hidden animate-fade-in"></div>
            <?php endif; ?>
            <button type="submit" id="registerButton" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-3 rounded-2xl transition-all duration-300 floating-action animate-slide-up">
                <i class="fas fa-user-plus mr-2"></i>Registrar
            </button>
        </form>
        <p class="text-center mt-4 text-sm text-white-600 dark:text-white-400">
            Já tem uma conta? <a href="login.php" class="text-blue-500 hover:text-blue-600 transition-colors">Faça Login</a>
        </p>
    </div>

    <script>
        // Carregar tema
        function loadTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark');
                document.getElementById('themeIcon').className = 'fas fa-sun mr-1';
            }
        }

        // Alternar tema
        function toggleTheme() {
            document.body.classList.toggle('dark');
            const icon = document.getElementById('themeIcon');
            const isDark = document.body.classList.contains('dark');
            icon.className = isDark ? 'fas fa-sun mr-1' : 'fas fa-moon mr-1';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        document.getElementById('toggleTheme').addEventListener('click', toggleTheme);

        // Validação e envio do formulário
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorMessage = document.getElementById('errorMessage');
            const registerButton = document.getElementById('registerButton');
            errorMessage.classList.add('hidden');

            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const profilePic = document.getElementById('profile_pic').files[0];

            // Validações no frontend
            if (!username || !email || !password || !confirmPassword) {
                errorMessage.textContent = 'Todos os campos obrigatórios devem ser preenchidos';
                errorMessage.classList.remove('hidden');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessage.textContent = 'Email inválido';
                errorMessage.classList.remove('hidden');
                return;
            }
            if (password !== confirmPassword) {
                errorMessage.textContent = 'As senhas não coincidem';
                errorMessage.classList.remove('hidden');
                return;
            }
            if (password.length < 8) {
                errorMessage.textContent = 'A senha deve ter no mínimo 8 caracteres';
                errorMessage.classList.remove('hidden');
                return;
            }
            if (profilePic) {
                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(profilePic.type)) {
                    errorMessage.textContent = 'A foto deve ser um arquivo JPEG ou PNG';
                    errorMessage.classList.remove('hidden');
                    return;
                }
                if (profilePic.size > 2 * 1024 * 1024) { // 2MB
                    errorMessage.textContent = 'A foto não pode exceder 2MB';
                    errorMessage.classList.remove('hidden');
                    return;
                }
            }

            // Mostrar estado de loading
            registerButton.disabled = true;
            registerButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...';

            // Enviar para o backend usando FormData
            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('confirm_password', confirmPassword);
                if (profilePic) {
                    formData.append('profile_pic', profilePic);
                }

                const response = await fetch('php/register.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
                }

                const result = await response.json();
                if (result.success) {
                    window.location.href = 'login.php';
                } else {
                    errorMessage.textContent = result.message;
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                errorMessage.textContent = 'Erro ao conectar com o servidor: ' + error.message;
                errorMessage.classList.remove('hidden');
            } finally {
                registerButton.disabled = false;
                registerButton.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Registrar';
            }
        });

        document.addEventListener('DOMContentLoaded', loadTheme);
    </script>
</body>
</html>