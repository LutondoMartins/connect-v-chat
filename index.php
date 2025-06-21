<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; 
}

// Get user info from session
$username = htmlspecialchars($_SESSION['username']);
$user_id = (int)$_SESSION['user_id']; // Garantir que user_id seja um inteiro

// Include database configuration
require_once 'php/config.php';

// Fetch user avatar (if stored in the database)
try {
    $stmt = $pdo->prepare('SELECT profile_pic FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Usar PDO::FETCH_ASSOC para garantir um array associativo
    if ($user && isset($user['profile_pic'])) {
        $avatar = $user['profile_pic'] ? 'uploads/' . htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/48';
    } else {
        // Usuário não encontrado, redirecionar para login
        error_log("Usuário com ID $user_id não encontrado na tabela users.");
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao consultar usuário: " . $e->getMessage());
    $avatar = 'https://via.placeholder.com/48'; // Fallback em caso de erro
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect-V - Modern Chat Experience</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

    <style>
               /* Modo light (padrão) */
        body {
            background: linear-gradient(135deg, #f8fafc, #e0e7ff);
            color: #1f2937;
        }

        /* Modo dark */
        .dark body {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: #f9fafb;
        }

        /* Sidebar */
        .dark #sidebar {
            background: rgba(17, 24, 39, 0.8);
            border-right-color: rgba(255, 255, 255, 0.1);
        }

        /* Área de chat principal */
        .dark .flex-1.flex.flex-col.bg-white\/30.backdrop-blur-sm {
            background: rgba(17, 24, 39, 0.3);
        }

        /* Header do chat */
        .dark #chatHeader {
            background: rgba(17, 24, 39, 0.8);
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        /* Área de mensagens */
        .dark #messagesContainer {
            background: linear-gradient(to bottom, rgba(31, 41, 55, 0.2), rgba(17, 24, 39, 0.2));
        }

        /* Input de mensagem */
        .dark #messageInput {
            background: rgba(31, 41, 55, 0.5);
            border-color: rgba(255, 255, 255, 0.1);
            color: #f9fafb;
        }

        /* Botões */
        .dark .bg-gradient-to-r.from-blue-500.to-blue-600 {
            background: linear-gradient(to right, #2563eb, #1e40af);
        }

        .dark .hover\:bg-gray-100 {
            background: rgba(55, 65, 81, 0.5);
        }

        /* Modais */
        .dark .bg-white.rounded-3xl {
            background: #1f2937;
            color: #f9fafb;
        }

        /* Chat items */
        .dark .chat-item {
            background: transparent;
        }

        .dark .chat-item:hover {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2));
        }

        .dark .chat-item.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
        }

        /* Bolhas de mensagem */
        .dark .message-bubble.bg-white\/80 {
            background: rgba(31, 41, 55, 0.8);
            border-color: rgba(255, 255, 255, 0.1);
            color: #f9fafb;
        }

        /* Outros elementos */
        .dark .text-gray-500 {
            color: #9ca3af;
        }

        .dark .text-gray-600 {
            color: #d1d5db;
        }

        .dark .bg-gray-100\/50 {
            background: rgba(55, 65, 81, 0.5);
        }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounceSubtle {
            0%, 20%, 53%, 80%, 100% { animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000); transform: translate3d(0,0,0); }
            40%, 43% { animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060); transform: translate3d(0, -8px, 0); }
            70% { animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060); transform: translate3d(0, -4px, 0); }
            90% { transform: translate3d(0,-1px,0); }
        }
        @keyframes glow {
            from { box-shadow: 0 0 20px -10px #3b82f6; }
            to { box-shadow: 0 0 20px -5px #3b82f6; }
        }

        .chat-scroll::-webkit-scrollbar { width: 4px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .dark .glass-morphism {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .message-bubble {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .message-bubble:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .typing-dot {
            animation: typingDot 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typingDot {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .chat-item {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .chat-item:hover {
            transform: translateX(4px);
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.1) 100%);
        }

        .chat-item.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-left: 3px solid #3b82f6;
        }

        .floating-action {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .floating-action:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        @media (max-width: 1023px) {
            #sidebar {
                width: 100vw;
                max-width: none;
            }
        }

        #mobileMenuBtn {
            z-index: 60;
            top: 1rem;
            left: 1rem;
        }

        .status-indicator {
            animation: pulse-slow 2s infinite;
        }

        .message-reactions {
            opacity: 1;
            transform: scale(1);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            margin-top: 0.5rem;
        }

        .message-reactions span {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.75rem;
        }

        .waveform {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .wave-bar {
            width: 3px;
            background: currentColor;
            border-radius: 2px;
            animation: waveAnimation 1s ease-in-out infinite;
        }

        .wave-bar:nth-child(1) { height: 8px; animation-delay: 0s; }
        .wave-bar:nth-child(2) { height: 16px; animation-delay: 0.1s; }
        .wave-bar:nth-child(3) { height: 12px; animation-delay: 0.2s; }
        .wave-bar:nth-child(4) { height: 20px; animation-delay: 0.3s; }
        .wave-bar:nth-child(5) { height: 10px; animation-delay: 0.4s; }

        @keyframes waveAnimation {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.3); }
        }

        /* Estilizar a lista de membros na modal */
        #groupMembers .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.2s;
        }

        #groupMembers .member-item:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .dark #groupMembers .member-item:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        /* Painel de detalhes do grupo */
        #groupDetails {
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #groupDetails.open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            #groupDetails {
                transform: none;
            }
        }

        .member-action {
            transition: all 0.2s;
        }

        .member-action:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .dark .member-action:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .chat-item, .message-bubble, #chatHeader, #chatStatus, #chatAvatar {
    transition: opacity 0.3s ease-in-out, background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
}

/* Estilo para mensagens não lidas */
.bg-blue-100.dark\:bg-blue-900\/50 {
    background-color: rgba(219, 234, 254, 0.5); /* Light mode */
}
.dark .bg-blue-100.dark\:bg-blue-900\/50 {
    background-color: rgba(30, 58, 138, 0.5); /* Dark mode */
}

/* Estilo para nome do remetente */
.message-bubble .sender-name {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280; /* text-gray-500 */
    margin-bottom: 0.25rem;
}
.dark .message-bubble .sender-name {
    color: #9ca3af; /* text-gray-400 */
}

/* Estilo para indicador de não lida */
.text-blue-500 {
    font-size: 0.75rem;
    font-weight: 500;
}
    </style>

</head>
<body class="min-h-screen overflow-hidden">
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" onclick="toggleMobileMenu()"></div>
    
    <div class="flex h-screen relative">
        <button id="mobileMenuBtn" 
                class="fixed top-4 left-4 z-50 lg:hidden bg-white dark:bg-gray-800 shadow-lg rounded-full p-3 floating-action"
                onclick="toggleMobileMenu()">
            <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
        </button>

        <div id="sidebar" class="w-80 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-r border-white/20 dark:border-gray-700/20 flex flex-col shadow-2xl mobile-menu lg:relative lg:translate-x-0 absolute inset-y-0 left-0 z-50">
            <div class="p-6 border-b border-gray-100/50 dark:border-gray-700/50 pt-16 lg:pt-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-white text-sm"></i>
                        </div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 dark:from-gray-200 dark:to-gray-400 bg-clip-text text-transparent">Connect-V</h1>
                    </div>
                    <div class="flex space-x-1">
                        <button onclick="openSearchUsersModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 group">
                            <i class="fas fa-search text-gray-500 dark:text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                        </button>
                        <button onclick="openCreateGroupModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 group">
                            <i class="fas fa-plus text-gray-500 dark:text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                        </button>
                        <button onclick="toggleTheme()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 group">
                            <i id="themeIcon" class="fas fa-moon text-gray-500 dark:text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                        </button>
                    </div>
                </div>
                
                <div class="relative group">
                    <input type="text" id="searchInput" placeholder="Pesquisar conversas..." 
                           class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-400 dark:placeholder-gray-500">
                    <i class="fas fa-search absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                    <div class="absolute right-3 top-3">
                        <kbd class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded opacity-50">⌘K</kbd>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto chat-scroll">
                <div class="p-3">
                    <div class="flex space-x-1 mb-4 bg-gray-100/50 dark:bg-gray-700/50 rounded-xl p-1">
                        <button class="flex-1 py-2 px-3 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm transition-all">
                            Todas
                        </button>
                        <button class="flex-1 py-2 px-3 text-sm font-medium rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all">
                            Não lidas
                        </button>
                        <button class="flex-1 py-2 px-3 text-sm font-medium rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all relative">
                            Grupos
                            <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                    
                    <div id="chatList" class="space-y-1"></div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100/50 dark:border-gray-700/50 bg-gradient-to-r from-blue-50/50 to-purple-50/50 dark:from-blue-900/50 dark:to-purple-900/50">
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <img id="userAvatar" src="<?php echo $avatar; ?>" 
                             alt="Perfil" class="w-12 h-12 rounded-full ring-2 ring-blue-500/20">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full status-indicator"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p id="userName" class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo $username; ?></p>
                        <p id="userStatus" class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                            Online agora
                        </p>
                    </div>
                    <div class="flex space-x-1">
                        <button class="p-2 hover:bg-white/50 dark:hover:bg-gray-700/50 rounded-xl transition-all duration-200">
                            <i class="fas fa-cog text-gray-500 dark:text-gray-300 hover:text-blue-500 transition-colors"></i>
                        </button>
                        <button class="p-2 hover:bg-white/50 dark:hover:bg-gray-700/50 rounded-xl transition-all duration-200">
                            <i class="fas fa-door-open text-gray-500 dark:text-gray-300 hover:text-blue-500 transition-colors"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col bg-white/30 dark:bg-gray-800/30 backdrop-blur-sm">
            <div id="chatHeader" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-b border-white/20 dark:border-gray-700/20 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 cursor-pointer" onclick="toggleGroupDetails()">
                        <div class="relative">
                            <img id="chatAvatar" src="https://via.placeholder.com/48" 
                                 alt="Avatar" class="w-12 h-12 rounded-full ring-2 ring-blue-500/20">
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                        </div>
                        <div>
                            <h2 id="chatName" class="font-semibold text-gray-900 dark:text-gray-100 text-lg">Selecione um chat</h2>
                            <p id="chatStatus" class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-1"></span>
                                Offline
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button class="p-3 hover:bg-blue-50 dark:hover:bg-blue-900/50 rounded-xl transition-all duration-200 group floating-action" onclick="startCall('audio')">
                            <i class="fas fa-phone text-gray-600 dark:text-gray-300 group-hover:text-blue-600 transition-colors"></i>
                        </button>
                        <button class="p-3 hover:bg-blue-50 dark:hover:bg-blue-900/50 rounded-xl transition-all duration-200 group floating-action" onclick="startCall('video')">
                            <i class="fas fa-video text-gray-600 dark:text-gray-300 group-hover:text-blue-600 transition-colors"></i>
                        </button>
                        <button class="p-3 hover:bg-blue-50 dark:hover:bg-blue-900/50 rounded-xl transition-all duration-200 group">
                            <i class="fas fa-ellipsis-v text-gray-600 dark:text-gray-300 group-hover:text-blue-600 transition-colors"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="messagesContainer" class="flex-1 overflow-y-auto chat-scroll p-6 bg-gradient-to-b from-blue-50/20 to-purple-50/20 dark:from-blue-900/20 dark:to-purple-900/20">
                <div id="messages" class="space-y-6 max-w-4xl mx-auto"></div>
                
                <div id="typingIndicator" class="hidden flex items-start space-x-3 mt-6 animate-fade-in max-w-4xl mx-auto">
                    <img src="https://via.placeholder.com/32" 
                         alt="Avatar" class="w-8 h-8 rounded-full">
                    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl px-4 py-3 shadow-lg border border-white/20 dark:border-gray-700/20 max-w-xs">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full typing-dot"></div>
                            <div class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full typing-dot"></div>
                            <div class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full typing-dot"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-t border-white/20 dark:border-gray-700/20 p-4 shadow-lg">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-end space-x-3">
                        <div class="flex space-x-2">
                            <button class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 group" onclick="toggleAttachMenu()">
                                <i class="fas fa-paperclip text-gray-500 dark:text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                            </button>
                            <button class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all duration-200 group" onclick="startVoiceRecord()">
                                <i class="fas fa-microphone text-gray-500 dark:text-gray-300 group-hover:text-red-500 transition-colors"></i>
                            </button>
                        </div>
                        
                        <div class="flex-1 relative">
                            <textarea id="messageInput" placeholder="Digite sua mensagem... (Shift + Enter para nova linha)" 
                                   rows="1"
                                   class="w-full px-4 py-3 pr-20 border border-gray-200 dark:border-gray-600 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-transparent resize-none transition-all duration-300 bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm dark:text-gray-200"
                                   style="max-height: 120px;"></textarea>
                            
                            <button class="absolute right-12 bottom-3 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-all" onclick="toggleEmojiPicker()">
                                <i class="fas fa-smile text-gray-500 dark:text-gray-300 hover:text-yellow-500 transition-colors"></i>
                            </button>
                            
                            <div class="absolute right-3 bottom-1 text-xs text-gray-400 dark:text-gray-500">
                                <span id="charCounter">0</span>/1000
                            </div>
                        </div>
                        
                        <button id="sendButton" onclick="sendMessage()" 
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-3 rounded-xl transition-all duration-300 floating-action disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    
                    <div id="attachMenu" class="hidden absolute bottom-20 left-20 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-600 p-2 animate-slide-up">
                        <button class="flex items-center space-x-3 w-full p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-all">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-blue-600 dark:text-blue-400 text-sm"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-200">Foto ou vídeo</span>
                        </button>
                        <button class="flex items-center space-x-3 w-full p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-all">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file text-green-600 dark:text-green-400 text-sm"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-200">Documento</span>
                        </button>
                        <button class="flex items-center space-x-3 w-full p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-all">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-purple-600 dark:text-purple-400 text-sm"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-200">Localização</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="groupDetails" class="hidden w-80 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-l border-white/20 dark:border-gray-700/20 flex flex-col shadow-2xl lg:relative lg:translate-x-0 absolute inset-y-0 right-0 z-50 mobile-menu">
            <div class="p-6 border-b border-gray-100/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Detalhes do Grupo</h2>
                    <button onclick="toggleGroupDetails()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                        <i class="fas fa-times text-gray-500 dark:text-gray-300"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto chat-scroll p-4">
                <div id="groupInfo" class="space-y-4"></div>
            </div>
        </div>
    </div>

    <div id="voiceModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-sm mx-4 text-center shadow-2xl">
            <div class="w-20 h-20 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-microphone text-red-500 text-2xl animate-pulse"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2 dark:text-gray-200">Gravando áudio...</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Toque para parar a gravação</p>
            <div class="waveform mb-4 justify-center">
                <div class="wave-bar bg-red-500"></div>
                <div class="wave-bar bg-red-500"></div>
                <div class="wave-bar bg-red-500"></div>
                <div class="wave-bar bg-red-500"></div>
                <div class="wave-bar bg-red-500"></div>
            </div>
            <button onclick="stopVoiceRecord()" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-full transition-all">
                Parar gravação
            </button>
        </div>
    </div>

    <div id="createGroupModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">Criar Novo Grupo</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome do Grupo</label>
                    <input id="groupName" type="text" placeholder="Digite o nome do grupo" 
                           class="w-full px-4 py-2 mt-1 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white/50 dark:bg-gray-700 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descrição</label>
                    <textarea id="groupDescription" placeholder="Descrição do grupo (opcional)" rows="3"
                              class="w-full px-4 py-2 mt-1 border border-gray-200 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white/50 dark:bg-gray-700 dark:text-gray-200"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Selecionar Membros</label>
                    <div id="groupMembers" class="space-y-2 mt-2 max-h-40 overflow-y-auto chat-scroll"></div>
                </div>
            </div>
            <div class="flex justify-end space-x-2 mt-6">
                <button onclick="closeCreateGroupModal()" class="px-4 py-2 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Cancelar</button>
                <button onclick="createGroup()" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all">Criar</button>
            </div>
        </div>
    </div>

    <div id="searchUsersModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">Pesquisar Usuários</h3>
            <div class="relative mb-4 flex items-center space-x-2">
                <input id="searchUsersInput" type="text" placeholder="Digite o nome do usuário..." 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50/50 dark:bg-gray-700 border-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white dark:focus:bg-gray-600 dark:text-gray-200 transition-all duration-300 placeholder-gray-400 dark:placeholder-gray-500">
                <i class="fas fa-search absolute left-4 top-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                <button id="searchUsersButton" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-xl transition-all duration-300 floating-action">
                    <i class="fas fa-search mr-1"></i>Pesquisar
                </button>
            </div>
            <div id="searchUsersResults" class="space-y-2 max-h-60 overflow-y-auto chat-scroll"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeSearchUsersModal()" class="px-4 py-2 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Fechar</button>
            </div>
        </div>
    </div>

<script>
let currentChat = null;
let messages = [];
let chats = [];
let isDarkMode = false;
let typingTimer;
let pollingInterval = null;
let chatsPollingInterval = null;
let lastMessageId = 0;
let currentUser = {
    username: '<?php echo htmlspecialchars($username); ?>',
    avatar: '<?php echo htmlspecialchars($avatar); ?>'
};
let currentFilter = 'all';

// Imagem padrão para usuários (fallback)
const DEFAULT_USER_AVATAR = 'https://via.placeholder.com/48';

function init() {
    console.log('Inicializando aplicativo...');
    loadTheme();
    setupEventListeners();
    setupVoiceCommand();
    initializeSearch();
    checkSession();
    startChatsPolling();
}

function loadTheme() {
    console.log('Carregando tema...');
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        isDarkMode = true;
        document.body.classList.add('dark');
        document.getElementById('themeIcon').className = 'fas fa-sun text-gray-500 group-hover:text-yellow-500 transition-colors';
    }
}

function toggleTheme() {
    console.log('Alternando tema...');
    isDarkMode = !isDarkMode;
    const icon = document.getElementById('themeIcon');
    if (isDarkMode) {
        document.body.classList.add('dark');
        icon.className = 'fas fa-sun text-gray-500 group-hover:text-yellow-500 transition-colors';
        localStorage.setItem('theme', 'dark');
    } else {
        document.body.classList.remove('dark');
        icon.className = 'fas fa-moon text-gray-500 group-hover:text-blue-500 transition-colors';
        localStorage.setItem('theme', 'light');
    }
}

async function checkSession() {
    console.log('Verificando sessão...');
    try {
        const response = await fetch('php/check_session.php', { 
            credentials: 'include',
            cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const result = await response.json();
        console.log('Resposta check_session:', result);
        if (!result.logged_in) {
            console.warn('Usuário não logado, redirecionando para login.php');
            window.location.href = 'login.php';
        } else {
            currentUser.username = result.username;
            currentUser.avatar = result.avatar || DEFAULT_USER_AVATAR;
            document.getElementById('userName').textContent = currentUser.username;
            document.getElementById('userAvatar').src = currentUser.avatar;
            document.getElementById('userStatus').textContent = 'Online agora';
            await loadChats();
        }
    } catch (error) {
        console.error('Erro ao verificar sessão:', error);
        window.location.href = 'login.php';
    }
}

async function loadChats() {
    console.log('Carregando chats...');
    try {
        const response = await fetch('php/get_chats.php', { 
            credentials: 'include',
            cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const result = await response.json();
        console.log('Resposta get_chats:', result);
        if (result.success) {
            chats = result.chats.map(chat => ({
                ...chat,
                unread_count: parseInt(chat.unread_count) || 0
            }));
            renderChatList();
            if (chats.length > 0 && !currentChat) {
                selectChat(chats[0]);
            } else if (chats.length === 0) {
                console.warn('Nenhum chat encontrado');
                document.getElementById('chatList').innerHTML = '<p class="text-gray-500 text-center">Nenhum chat disponível</p>';
            }
        } else {
            console.error('Erro ao carregar chats:', result.message);
            document.getElementById('chatList').innerHTML = `<p class="text-red-500 text-center">Erro: ${result.message}</p>`;
        }
    } catch (error) {
        console.error('Erro ao carregar chats:', error);
        document.getElementById('chatList').innerHTML = '<p class="text-red-500 text-center">Erro ao conectar com o servidor</p>';
    }
}

function startChatsPolling() {
    stopChatsPolling();
    console.log('Iniciando polling de chats...');
    chatsPollingInterval = setInterval(async () => {
        try {
            await loadChats();
        } catch (error) {
            console.error('Erro no polling de chats:', error);
        }
    }, 10000);
}

function stopChatsPolling() {
    if (chatsPollingInterval) {
        clearInterval(chatsPollingInterval);
        chatsPollingInterval = null;
    }
}

function renderChatList() {
    console.log('Renderizando lista de chats:', chats);
    const chatList = document.getElementById('chatList');
    const currentChatId = currentChat?.id;
    const existingChats = new Map(Array.from(chatList.children).map(item => [parseInt(item.dataset.chatId), item]));

    chats.forEach((chat, index) => {
        const chatItem = existingChats.get(chat.id) || document.createElement('div');
        chatItem.dataset.chatId = chat.id;
        chatItem.className = `chat-item p-4 cursor-pointer rounded-xl mb-2 ${chat.id === currentChatId ? 'active' : ''} ${chat.unread_count > 0 ? 'bg-blue-50 dark:bg-blue-900/30' : ''}`;
        chatItem.onclick = () => selectChat(chat);

        if (!existingChats.has(chat.id)) {
            chatItem.style.animationDelay = `${index * 50}ms`;
            chatItem.classList.add('animate-fade-in');
        }

        const unreadBadge = chat.unread_count > 0 ? 
            `<div class="ml-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-medium">${chat.unread_count > 99 ? '99+' : chat.unread_count}</div>` : '';
        const unreadDot = chat.unread_count > 0 && chat.id !== currentChatId ? '<span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>' : '';
        const onlineIndicator = chat.is_group ? '' : (chat.status === 'online' ? 
            '<div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>' : '');
        const time = chat.last_message_time ? formatLastSeen(new Date(chat.last_message_time)) : '';
        const groupIcon = chat.is_group ? '<i class="fas fa-users text-xs text-gray-400 dark:text-gray-500 mr-1"></i>' : '';
        const avatarSrc = chat.avatar || DEFAULT_USER_AVATAR;
        const displayName = chat.display_name || chat.name;
        const lastMessage = chat.last_message ? 
            `${chat.last_sender_name ? `<span class="font-semibold">${chat.last_sender_name}:</span> ` : ''}${chat.last_message}` : 
            'Nenhuma mensagem';
        chatItem.innerHTML = `
            <div class="relative">
                ${unreadDot}
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <img src="${avatarSrc}" alt="${displayName}" class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent hover:ring-blue-200 transition-all">
                        ${onlineIndicator}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate flex items-center">
                                ${groupIcon}${displayName}
                            </h3>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">${time}</span>
                                ${unreadBadge}
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-1">${lastMessage}</p>
                    </div>
                </div>
            </div>
        `;
        if (!existingChats.has(chat.id)) {
            chatList.appendChild(chatItem);
        }
        existingChats.delete(chat.id);
    });

    existingChats.forEach((_, chatId) => {
        const item = chatList.querySelector(`[data-chat-id="${chatId}"]`);
        if (item) item.remove();
    });
}

async function selectChat(chat) {
    if (currentChat?.id === chat.id) {
        console.log('Chat já selecionado:', chat.id);
        return;
    }
    console.log('Selecionando chat:', chat);
    stopPolling();
    currentChat = chat;
    lastMessageId = 0;
    messages = [];
    document.getElementById('messages').innerHTML = '';
    updateChatHeader(chat);
    await loadChatMessages(chat.id);
    await markMessagesAsRead(chat.id);
    startPolling(chat.id);
    renderGroupDetails(chat);
    if (window.innerWidth < 1024) {
        toggleMobileMenu();
        document.getElementById('groupDetails').classList.remove('open');
        document.querySelector('.flex-1.flex.flex-col.bg-white\\/30.backdrop-blur-sm').style.display = 'flex';
    }
}

async function markMessagesAsRead(chatId) {
    console.log('Marcando mensagens como lidas para chat_id:', chatId);
    try {
        const response = await fetch('php/mark_messages_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ chat_id: chatId }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta mark_messages_read:', result);
        if (result.success) {
            const chat = chats.find(c => c.id === chatId);
            if (chat) {
                chat.unread_count = 0;
                renderChatList();
            }
        } else {
            console.error('Erro ao marcar mensagens como lidas:', result.message);
        }
    } catch (error) {
        console.error('Erro ao marcar mensagens como lidas:', error);
    }
}

function updateChatHeader(chat) {
    console.log('Atualizando cabeçalho do chat:', chat);
    const header = document.getElementById('chatHeader');
    header.style.transition = 'opacity 0.3s ease-in-out';
    header.style.opacity = '0.7';
    setTimeout(() => {
        document.getElementById('chatName').textContent = chat.name;
        // Usar avatar retornado pelo backend (já inclui default_group_avatar para grupos)
        const avatarSrc = chat.avatar || DEFAULT_USER_AVATAR;
        document.getElementById('chatAvatar').src = avatarSrc;
        // Exibir número de membros para grupos, status para chats individuais
        const status = chat.is_group ? 'Grupo' : (chat.status === 'online' ? 'Online agora' : `Visto por último ${formatLastSeen(new Date(chat.last_seen))}`);
        document.getElementById('chatStatus').innerHTML = `
            <span class="w-2 h-2 ${chat.is_group ? 'bg-blue-500' : chat.status === 'online' ? 'bg-green-500' : 'bg-gray-400'} rounded-full mr-1 transition-all duration-300"></span>
            ${status}
        `;
        header.style.opacity = '1';
    }, 150);
}

function formatLastSeen(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    if (isNaN(minutes)) return 'desconhecido';
    if (minutes < 1) return 'agora mesmo';
    if (minutes < 60) return `${minutes}m`;
    if (hours < 24) return `${hours}h`;
    return `${days}d`;
}

async function loadChatMessages(chatId) {
    console.log('Carregando mensagens para chat_id:', chatId, 'last_message_id:', lastMessageId);
    try {
        const response = await fetch(`php/get_messages.php?chat_id=${chatId}&last_message_id=${lastMessageId}`, {
            credentials: 'include',
            cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        const result = await response.json();
        console.log('Resposta get_messages:', result);
        if (result.success) {
            const newMessages = result.messages;
            if (newMessages.length > 0) {
                const filteredMessages = newMessages.filter(msg => msg.chat_id === chatId);
                if (filteredMessages.length !== newMessages.length) {
                    console.warn('Mensagens de outros chats detectadas:', newMessages);
                }
                messages = [...messages, ...filteredMessages];
                lastMessageId = Math.max(...filteredMessages.map(m => m.id), lastMessageId);
                renderMessages();
            } else {
                console.log('Nenhuma nova mensagem encontrada');
                if (messages.length === 0) {
                    document.getElementById('messages').innerHTML = '<p class="text-gray-500 text-center">Nenhuma mensagem</p>';
                }
            }
        } else {
            console.error('Erro ao carregar mensagens:', result.message);
            document.getElementById('messages').innerHTML = `<p class="text-red-500 text-center">Erro: ${result.message}</p>`;
        }
    } catch (error) {
        console.error('Erro ao carregar mensagens:', error);
        document.getElementById('messages').innerHTML = '<p class="text-red-500 text-center">Erro ao conectar com o servidor</p>';
    }
}

function startPolling(chatId) {
    stopPolling();
    console.log('Iniciando polling de mensagens para chat_id:', chatId);
    pollingInterval = setInterval(async () => {
        try {
            if (currentChat?.id === chatId) {
                await loadChatMessages(chatId);
            }
        } catch (error) {
            console.error('Erro no polling de mensagens:', error);
        }
    }, 5000);
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

function renderMessages() {
    console.log('Renderizando mensagens para chat_id:', currentChat?.id, 'mensagens:', messages);
    const messagesContainer = document.getElementById('messages');
    messagesContainer.innerHTML = '';
    if (messages.length === 0) {
        messagesContainer.innerHTML = '<p class="text-gray-500 text-center">Nenhuma mensagem</p>';
        return;
    }
    const existingMessages = new Map();
    const scrollPosition = messagesContainer.scrollHeight - messagesContainer.scrollTop;
    const wasAtBottom = scrollPosition <= messagesContainer.clientHeight + 50;

    messages.forEach((message, index) => {
        if (!existingMessages.has(message.id) && message.chat_id === currentChat?.id) {
            console.log('Renderizando mensagem individual:', message);
            const messageDiv = document.createElement('div');
            messageDiv.dataset.messageId = message.id;
            messageDiv.className = `flex ${message.sent ? 'justify-end' : 'justify-start'} animate-slide-up`;
            messageDiv.style.animationDelay = `${index * 100}ms`;
            const messageWithContent = { ...message, content: message.text };
            const messageContent = createMessageBubble(messageWithContent);
            const senderName = message.sent ? '' : `<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">${message.username}</p>`;
            const unreadIndicator = !message.sent && !message.is_read ? '<span class="text-xs text-blue-500">Não lida</span>' : '';
            if (!message.sent) {
                messageDiv.innerHTML = `
                    <div class="flex items-end space-x-2 max-w-xs lg:max-w-md">
                        <img src="${message.avatar || DEFAULT_USER_AVATAR}" alt="${message.username}" class="w-8 h-8 rounded-full object-cover">
                        <div>
                            ${senderName}
                            ${messageContent}
                            ${unreadIndicator}
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="max-w-xs lg:max-w-md">
                        ${senderName}
                        ${messageContent}
                        ${unreadIndicator}
                    </div>
                `;
            }
            messagesContainer.appendChild(messageDiv);
            existingMessages.set(message.id, true);
        }
    });

    if (wasAtBottom) {
        setTimeout(() => {
            const container = document.getElementById('messagesContainer');
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }, 300);
    }
}

function createMessageBubble(message) {
    console.log('Criando balão de mensagem:', message);
    const bubbleClass = message.sent ? 
        'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 
        `bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm text-gray-800 dark:text-gray-100 border border-white/20 dark:border-gray-700/20 ${!message.is_read && !message.sent ? 'bg-blue-100 dark:bg-blue-900/50' : ''}`;
    let content = '';
    switch (message.type) {
        case 'image':
            content = `
                <div class="flex items-center space-x-3 p-1">
                    <img src="Uploads/${message.file_name}" alt="${message.file_name}" class="w-32 h-32 object-cover rounded-lg">
                </div>
            `;
            break;
        case 'file':
            content = `
                <div class="flex items-center space-x-3 p-1">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-pdf text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <a href="Uploads/${message.file_name}" target="_blank" class="text-sm font-medium">${message.file_name}</a>
                        <p class="text-xs opacity-70">${message.file_size}</p>
                    </div>
                </div>
            `;
            break;
        case 'voice':
            content = `<p class="text-sm leading-relaxed">🎙️ Mensagem de voz</p>`;
            break;
        default:
            content = `<p class="text-sm leading-relaxed">${message.content || 'Mensagem vazia'}</p>`;
    }
    const reactions = `
        <div class="message-reactions flex space-x-1 mt-2">
            ${message.reactions && message.reactions.length ? 
                message.reactions.map(r => `<span class="text-xs bg-white/20 dark:bg-gray-700/20 rounded-full px-2 py-1">${r}</span>`).join('') : 
                '<span class="text-xs text-gray-400 dark:text-gray-500"></span>'}
        </div>
    `;
    return `
        <div class="message-bubble ${bubbleClass} rounded-2xl px-4 py-3 shadow-lg relative">
            ${content}
            ${reactions}
            <div class="text-xs opacity-70 mt-2 ${message.sent ? 'text-right' : 'text-left'}">${message.time}</div>
            <div class="flex space-x-1 mt-2 justify-end">
                <button onclick="reactToMessage(${message.id}, '👍')" class="hover:scale-125 transition-transform text-sm">👍</button>
                <button onclick="reactToMessage(${message.id}, '❤️')" class="hover:scale-125 transition-transform text-sm">❤️</button>
                <button onclick="reactToMessage(${message.id}, '😊')" class="hover:scale-125 transition-transform text-sm">😊</button>
            </div>
        </div>
    `;
}

async function reactToMessage(messageId, reaction) {
    console.log('Reagindo à mensagem:', { messageId, reaction });
    try {
        const response = await fetch('php/react_to_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ message_id: messageId, reaction }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta react_to_message:', result);
        if (result.success) {
            const message = messages.find(m => m.id === messageId);
            if (message) {
                message.reactions = result.reactions || [];
                renderMessages();
            }
        } else {
            console.error('Erro ao reagir:', result.message);
        }
    } catch (error) {
        console.error('Erro ao reagir:', error);
    }
}

async function sendMessage(chatId, messageInput) {
    console.log('Enviando mensagem para chat_id:', chatId, 'conteúdo:', messageInput.value);
    try {
        const message = messageInput.value.trim();
        if (!message) {
            alert('A mensagem não pode estar vazia');
            return;
        }
        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('message', message);
        formData.append('type', 'text');

        const response = await fetch('php/send_message.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta send_message:', result);

        if (result.success) {
            messageInput.value = '';
            await loadChatMessages(chatId);
            const container = document.getElementById('messagesContainer');
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            console.error('Erro ao enviar mensagem:', result.message);
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        alert('Erro ao conectar com o servidor');
    }
}

function toggleMobileMenu() {
    console.log('Alternando menu móvel...');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    const menuBtn = document.getElementById('mobileMenuBtn');
    const mainChatArea = document.querySelector('.flex-1.flex.flex-col.bg-white\\/30.backdrop-blur-sm');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('hidden');
    if (sidebar.classList.contains('open')) {
        mainChatArea.style.display = 'none';
    } else {
        mainChatArea.style.display = 'flex';
    }
    const icon = menuBtn.querySelector('i');
    icon.className = sidebar.classList.contains('open') ? 
        'fas fa-times text-gray-700 dark:text-gray-300' : 
        'fas fa-bars text-gray-700 dark:text-gray-300';
}

function toggleAttachMenu() {
    console.log('Alternando menu de anexos...');
    const attachMenu = document.getElementById('attachMenu');
    attachMenu.classList.toggle('hidden');
}

function toggleEmojiPicker() {
    console.log('Tentando abrir seletor de emojis...');
    alert('Emoji picker ainda não implementado!');
}

function startVoiceRecord() {
    console.log('Iniciando gravação de voz...');
    const voiceModal = document.getElementById('voiceModal');
    voiceModal.classList.remove('hidden');
}

async function stopVoiceRecord() {
    console.log('Parando gravação de voz...');
    const voiceModal = document.getElementById('voiceModal');
    voiceModal.classList.add('hidden');
    if (!currentChat) return;
    try {
        const response = await fetch('php/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ chat_id: currentChat.id, message: 'Mensagem de voz', type: 'voice' }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta send_message (voz):', result);
        if (result.success) {
            await loadChatMessages(currentChat.id);
            await loadChats();
        }
    } catch (error) {
        console.error('Erro ao enviar mensagem de voz:', error);
    }
}

function startCall(type) {
    console.log('Iniciando chamada:', type);
    alert(`Iniciando ${type === 'audio' ? 'chamada de áudio' : 'chamada de vídeo'}...`);
}

function openCreateGroupModal() {
    console.log('Abrindo modal de criação de grupo...');
    const modal = document.getElementById('createGroupModal');
    modal.classList.remove('hidden');
    renderGroupMembers();
}

function closeCreateGroupModal() {
    console.log('Fechando modal de criação de grupo...');
    const modal = document.getElementById('createGroupModal');
    modal.classList.add('hidden');
    document.getElementById('groupName').value = '';
    document.getElementById('groupDescription').value = '';
}

async function renderGroupMembers() {
    console.log('Renderizando membros do grupo...');
    const membersContainer = document.getElementById('groupMembers');
    membersContainer.innerHTML = '';
    try {
        const response = await fetch('php/get_users.php', { credentials: 'include' });
        const data = await response.json();
        console.log('Resposta get_users:', data);
        if (data.success) {
            data.users.forEach(user => {
                const memberItem = document.createElement('div');
                memberItem.className = 'member-item';
                memberItem.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <img src="${user.avatar || DEFAULT_USER_AVATAR}" alt="${user.name}" class="w-8 h-8 rounded-full">
                        <span class="text-sm text-gray-700 dark:text-gray-200">${user.name}</span>
                    </div>
                    <input type="checkbox" class="member-checkbox" data-id="${user.id}">
                `;
                membersContainer.appendChild(memberItem);
            });
        } else {
            membersContainer.innerHTML = '<p class="text-red-500">Erro: ' + data.message + '</p>';
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        membersContainer.innerHTML = '<p class="text-red-500">Erro ao conectar com o servidor</p>';
    }
}

async function createGroup() {
    console.log('Criando grupo...');
    const name = document.getElementById('groupName').value.trim();
    const description = document.getElementById('groupDescription').value.trim();
    const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => parseInt(cb.dataset.id));
    if (!name) {
        alert('O nome do grupo é obrigatório!');
        return;
    }
    if (selectedMembers.length < 2) {
        alert('Selecione pelo menos 2 membros para criar um grupo!');
        return;
    }
    try {
        const response = await fetch('php/create_group.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                name,
                description,
                members: JSON.stringify(selectedMembers)
            }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta create_group:', result);
        if (result.success) {
            await loadChats();
            closeCreateGroupModal();
            const newGroup = chats.find(c => c.id === result.group_id);
            if (newGroup) selectChat(newGroup);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao criar grupo:', error);
        alert('Erro ao criar grupo');
    }
}

function openSearchUsersModal() {
    console.log('Abrindo modal de busca de usuários...');
    const modal = document.getElementById('searchUsersModal');
    modal.classList.remove('hidden');
    renderSearchUsersResults('');
    const searchInput = document.getElementById('searchUsersInput');
    const searchButton = document.getElementById('searchUsersButton');
    
    searchInput.focus();
    
    const newSearchButton = searchButton.cloneNode(true);
    searchButton.parentNode.replaceChild(newSearchButton, searchButton);
    
    newSearchButton.addEventListener('click', () => {
        renderSearchUsersResults(searchInput.value);
    });
    
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            renderSearchUsersResults(searchInput.value);
        }
    });
}

function closeSearchUsersModal() {
    console.log('Fechando modal de busca de usuários...');
    const modal = document.getElementById('searchUsersModal');
    modal.classList.add('hidden');
    document.getElementById('searchUsersInput').value = '';
}

async function renderSearchUsersResults(query) {
    console.log('Buscando usuários com query:', query);
    const resultsContainer = document.getElementById('searchUsersResults');
    resultsContainer.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Carregando...</p>';
    try {
        const response = await fetch(`php/search_users.php?query=${encodeURIComponent(query)}`, { 
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        console.log('Resposta search_users:', data);
        resultsContainer.innerHTML = '';
        let users = data.success ? data.users : [];
        if (users.length === 0) {
            resultsContainer.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Nenhum usuário encontrado.</p>';
            return;
        }
        users.forEach(user => {
            const userItem = document.createElement('div');
            userItem.className = 'flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl cursor-pointer';
            userItem.onclick = () => startChatWithUser(user);
            userItem.innerHTML = `
                <img src="${user.avatar || DEFAULT_USER_AVATAR}" alt="${user.name}" class="w-10 h-10 rounded-full">
                <span class="text-sm text-gray-700 dark:text-gray-200">${user.name}</span>
            `;
            resultsContainer.appendChild(userItem);
        });
    } catch (error) {
        console.error('Erro ao buscar usuários:', error);
        resultsContainer.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Erro ao conectar com o servidor.</p>';
    }
}

async function startChatWithUser(user) {
    console.log('Iniciando chat com usuário:', user);
    try {
        const response = await fetch('php/start_chat.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded', 
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ user_id: user.id }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta start_chat:', result);
        if (result.success) {
            await loadChats();
            const chatId = parseInt(result.chat_id);
            const chat = chats.find(c => c.id === chatId);
            if (chat) {
                chat.name = user.name;
                chat.avatar = user.avatar || DEFAULT_USER_AVATAR;
                selectChat(chat);
                closeSearchUsersModal();
            } else {
                console.error('Chat não encontrado na lista');
                alert('Chat criado, mas não encontrado na lista.');
            }
        } else {
            console.error('Erro ao criar chat:', result.message);
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao iniciar chat:', error);
        alert('Erro ao conectar com o servidor.');
    }
}

function toggleGroupDetails() {
    console.log('Alternando detalhes do grupo...');
    const groupDetails = document.getElementById('groupDetails');
    groupDetails.classList.toggle('open');
    if (window.innerWidth < 1024) {
        const mainChatArea = document.querySelector('.flex-1.flex.flex-col.bg-white\\/30.backdrop-blur-sm');
        mainChatArea.style.display = groupDetails.classList.contains('open') ? 'none' : 'flex';
    }
}

async function renderGroupDetails(chat) {
    console.log('Renderizando detalhes do grupo:', chat);
    if (!chat.is_group) {
        console.log('Chat não é grupo, ocultando painel de detalhes');
        document.getElementById('groupDetails').classList.add('hidden');
        return;
    }
    document.getElementById('groupDetails').classList.remove('hidden');
    const groupInfo = document.getElementById('groupInfo');
    try {
        const response = await fetch(`php/get_group_members.php?chat_id=${chat.id}`, { credentials: 'include' });
        const result = await response.json();
        console.log('Resposta get_group_members:', result);
        if (result.success) {
            const members = result.members;
            console.log('Membros do grupo:', members.map(m => ({ name: m.name, avatar: m.avatar || 'default' })));
            groupInfo.innerHTML = `
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">Nome</h3>
                    <p class="text-gray-900 dark:text-gray-100">${chat.name}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">Descrição</h3>
                    <p class="text-gray-600 dark:text-gray-400">${chat.description || 'Nenhuma descrição'}</p>
                </div>
                <div>
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">Membros (${members.length})</h3>
                        <button onclick="openAddMemberModal(${chat.id})" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            <i class="fas fa-user-plus text-blue-500"></i>
                        </button>
                    </div>
                    <div class="space-y-2 mt-2">
                        ${members.map(member => `
                            <div class="flex items-center justify-between p-2 member-action rounded-xl">
                                <div class="flex items-center space-x-2">
                                    <img src="${member.avatar || DEFAULT_USER_AVATAR}" alt="${member.name}" class="w-8 h-8 rounded-full">
                                    <span class="text-sm text-gray-700 dark:text-gray-200">${member.name}</span>
                                </div>
                                <button onclick="removeMember(${chat.id}, ${member.id})" class="p-2 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-xl">
                                    <i class="fas fa-trash text-red-500"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else {
            console.error('Erro ao carregar membros:', result.message);
            groupInfo.innerHTML = `<p class="text-red-500">Erro: ${result.message}</p>`;
        }
    } catch (error) {
        console.error('Erro ao carregar membros do grupo:', error);
        groupInfo.innerHTML = '<p class="text-red-500">Erro ao conectar com o servidor</p>';
    }
}

async function openAddMemberModal(groupId) {
    console.log('Abrindo modal para adicionar membros ao grupo:', groupId);
    const modal = document.createElement('div');
    modal.id = 'addMemberModal';
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-60 flex items-center justify-center';
    try {
        const response = await fetch(`php/get_users.php?exclude_group=${groupId}`, { credentials: 'include' });
        const data = await response.json();
        console.log('Resposta get_users (add members):', data);
        if (data.success) {
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
                    <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">Adicionar Membro</h3>
                    <div id="addMemberList" class="space-y-2 max-h-60 overflow-y-auto chat-scroll">
                        ${data.users.map(user => `
                            <div class="flex items-center space-x-3 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                                <img src="${user.avatar || DEFAULT_USER_AVATAR}" alt="${user.name}" class="w-10 h-10 rounded-full">
                                <span class="text-sm text-gray-700 dark:text-gray-200">${user.name}</span>
                                <input type="checkbox" class="add-member-checkbox" data-id="${user.id}">
                            </div>
                        `).join('')}
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button onclick="this.closest('#addMemberModal').remove()" class="px-4 py-2 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Cancelar</button>
                        <button onclick="addMembers(${groupId})" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all">Adicionar</button>
                    </div>
                </div>
            `;
        } else {
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
                    <p class="text-red-500">Erro: ${data.message}</p>
                    <button onclick="this.closest('#addMemberModal').remove()" class="mt-4 px-4 py-2 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Fechar</button>
                </div>
            `;
        }
        document.body.appendChild(modal);
    } catch (error) {
        console.error('Erro ao carregar usuários para adicionar:', error);
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl">
                <p class="text-red-500">Erro ao conectar com o servidor</p>
                <button onclick="this.closest('#addMemberModal').remove()" class="mt-4 px-4 py-2 text-gray-600 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Fechar</button>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

async function addMembers(groupId) {
    console.log('Adicionando membros ao grupo:', groupId);
    const selectedMembers = Array.from(document.querySelectorAll('.add-member-checkbox:checked')).map(cb => parseInt(cb.dataset.id));
    if (selectedMembers.length === 0) {
        alert('Selecione pelo menos um membro!');
        return;
    }
    try {
        const response = await fetch('php/add_group_members.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                chat_id: groupId,
                members: JSON.stringify(selectedMembers)
            }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta add_group_members:', result);
        if (result.success) {
            renderGroupDetails(chats.find(c => c.id === groupId));
            document.getElementById('addMemberModal').remove();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao adicionar membros:', error);
    }
}

async function removeMember(groupId, memberId) {
    console.log('Removendo membro do grupo:', { groupId, memberId });
    try {
        const response = await fetch('php/remove_group_member.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ chat_id: groupId, member_id: memberId }),
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta remove_group_member:', result);
        if (result.success) {
            renderGroupDetails(chats.find(c => c.id === groupId));
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao remover membro:', error);
    }
}

async function handleFileUpload(event) {
    console.log('Fazendo upload de arquivo...');
    const file = event.target.files[0];
    if (!file || !currentChat) return;
    const formData = new FormData();
    formData.append('chat_id', currentChat.id);
    formData.append('file', file);
    try {
        const response = await fetch('php/upload_file.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const result = await response.json();
        console.log('Resposta upload_file:', result);
        if (result.success) {
            await loadChatMessages(currentChat.id);
            await loadChats();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Erro ao fazer upload do arquivo:', error);
    }
}

async function logout() {
    console.log('Fazendo logout...');
    try {
        const response = await fetch('php/logout.php', { credentials: 'include' });
        const result = await response.json();
        console.log('Resposta logout:', result);
        if (result.success) {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Erro ao fazer logout:', error);
    }
}

function setupEventListeners() {
    console.log('Configurando event listeners...');
    const messageInput = document.getElementById('messageInput');
    messageInput.addEventListener('input', () => {
        updateCharCounter();
        autoResizeTextarea();
    });
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (currentChat) {
                sendMessage(currentChat.id, messageInput);
            } else {
                alert('Selecione um chat antes de enviar uma mensagem');
            }
        }
    });
    const sendButton = document.querySelector('button[onclick="sendMessage()"]') || document.getElementById('sendButton');
    if (sendButton) {
        sendButton.onclick = () => {
            if (currentChat) {
                sendMessage(currentChat.id, messageInput);
            } else {
                alert('Selecione um chat antes de enviar uma mensagem');
            }
        };
    }
    window.addEventListener('resize', handleResize);
    document.addEventListener('click', (e) => {
        const attachMenu = document.getElementById('attachMenu');
        if (!e.target.closest('[onclick="toggleAttachMenu()"]') && !attachMenu.contains(e.target)) {
            attachMenu.classList.add('hidden');
        }
    });
    const photoButton = document.querySelector('#attachMenu button:first-child');
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,application/pdf';
    fileInput.style.display = 'none';
    fileInput.addEventListener('change', handleFileUpload);
    document.body.appendChild(fileInput);
    photoButton.addEventListener('click', () => fileInput.click());
    document.querySelector('.p-4.border-t button:last-child').addEventListener('click', logout);

    // Adicionar event listeners para filtros
    const filterButtons = document.querySelectorAll('#sidebar .flex.space-x-1.mb-4 button');
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-white', 'dark:bg-gray-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
                btn.classList.add('text-gray-500', 'dark:text-gray-400');
            });
            button.classList.add('bg-white', 'dark:bg-gray-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
            button.classList.remove('text-gray-500', 'dark:text-gray-400');
            currentFilter = button.textContent.trim().toLowerCase();
            renderChatList();
        });
    });
}

function updateCharCounter() {
    const input = document.getElementById('messageInput');
    const counter = document.getElementById('charCounter');
    counter.textContent = input.value.length;
}

function autoResizeTextarea() {
    const textarea = document.getElementById('messageInput');
    textarea.style.height = 'auto';
    textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
}

function handleResize() {
    console.log('Ajustando layout para resize...');
    if (window.innerWidth >= 1024) {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('mobileOverlay').classList.add('hidden');
        document.getElementById('mobileMenuBtn').querySelector('i').className = 'fas fa-bars text-gray-700 dark:text-gray-300';
        document.querySelector('.flex-1.flex.flex-col.bg-white\\/30.backdrop-blur-sm').style.display = 'flex';
        document.getElementById('groupDetails').classList.remove('open');
    }
}

function initializeSearch() {
    console.log('Inicializando busca...');
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', async (e) => {
        const query = e.target.value.toLowerCase();
        try {
            const response = await fetch(`php/search_chats.php?query=${encodeURIComponent(query)}`, { credentials: 'include' });
            const result = await response.json();
            console.log('Resposta search_chats:', result);
            if (result.success) {
                chats = result.chats;
                renderChatList();
            }
        } catch (error) {
            console.error('Erro ao buscar chats:', error);
        }
    });
}

function setupVoiceCommand() {
    console.log('Configurando comando de voz...');
    document.addEventListener('keydown', (e) => {
        if (e.metaKey && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    init();
});
</script>

    
</body>
</html>