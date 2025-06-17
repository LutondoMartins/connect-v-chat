let currentChat = null;
let messages = [];
let isDarkMode = false;
let typingTimer;
let pollingInterval;

// Função de inicialização
function init() {
    loadTheme();
    setupEventListeners();
    setupVoiceCommand();
    initializeSearch();
    startPolling();
}

// Carregar tema
function loadTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        isDarkMode = true;
        document.body.classList.add('dark');
        document.getElementById('themeIcon').className = 'fas fa-sun text-gray-500 group-hover:text-yellow-500 transition-colors';
    }
}

// Alternar tema
function toggleTheme() {
    isDarkMode = !isDarkMode;
    const icon = document.getElementById('themeIcon');
    document.body.classList.toggle('dark');
    icon.className = isDarkMode ? 'fas fa-sun text-gray-500 group-hover:text-yellow-500 transition-colors' : 'fas fa-moon text-gray-500 group-hover:text-blue-500 transition-colors';
    localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
}

// Logout
function logout() {
    fetch('php/logout.php', { method: 'POST' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                window.location.href = 'login.html';
            } else {
                alert('Erro ao sair: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            alert('Erro ao conectar com o servidor');
        });
}

// Listar chats
function renderChatList() {
    const chatList = document.getElementById('chatList');
    chatList.innerHTML = '';

    fetch('php/get_chats.php')
        .then(response => response.json())
        .then(chats => {
            chats.forEach((chat, index) => {
                const chatItem = document.createElement('div');
                chatItem.className = `chat-item p-4 cursor-pointer rounded-xl mb-2 ${chat.id === currentChat ? 'active' : ''}`;
                chatItem.onclick = () => selectChat(chat);
                chatItem.style.animationDelay = `${index * 50}ms`;
                chatItem.classList.add('animate-fade-in');

                const unreadBadge = chat.unread_count > 0 ? 
                    `<div class="ml-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-medium animate-bounce-subtle">${chat.unread_count > 99 ? '99+' : chat.unread_count}</div>` : '';

                const onlineIndicator = chat.status === 'online' ? 
                    '<div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>' : '';

                const groupIcon = chat.is_group ? '<i class="fas fa-users text-xs text-gray-400 dark:text-gray-500 mr-1"></i>' : '';

                chatItem.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <img src="${chat.avatar || 'https://via.placeholder.com/48'}" alt="${chat.name}" class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent hover:ring-blue-200 transition-all">
                            ${onlineIndicator}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate flex items-center">
                                    ${groupIcon}${chat.name}
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">${chat.last_message_time || ''}</span>
                                    ${unreadBadge}
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-1">${chat.last_message || ''}</p>
                        </div>
                    </div>
                `;
                chatList.appendChild(chatItem);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar chats:', error);
            chatList.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Erro ao carregar conversas.</p>';
        });
}

// Selecionar chat
function selectChat(chat) {
    if (currentChat === chat.id) return;
    currentChat = chat.id;
    updateChatHeader(chat);
    loadChatMessages(chat.id);
    renderGroupDetails(chat);
    
    if (window.innerWidth < 1024) {
        toggleMobileMenu();
        document.getElementById('groupDetails').classList.remove('open');
        document.querySelector('.flex-1.flex.flex-col.bg-white\\/30.backdrop-blur-sm').style.display = 'flex';
    }
}

// Atualizar cabeçalho do chat
function updateChatHeader(chat) {
    const header = document.getElementById('chatHeader');
    header.style.opacity = '0.7';
    
    setTimeout(() => {
        document.getElementById('chatName').textContent = chat.name;
        document.getElementById('chatAvatar').src = chat.avatar || 'https://via.placeholder.com/48';
        const status = chat.status === 'online' ? 'Online agora' : `Visto por último ${formatLastSeen(chat.last_seen)}`;
        document.getElementById('chatStatus').innerHTML = `
            <span class="w-2 h-2 ${chat.status === 'online' ? 'bg-green-500 animate-pulse' : 'bg-gray-400'} rounded-full mr-1"></span>
            ${status}
        `;
        header.style.opacity = '1';
    }, 150);
}

// Formatar última visualização
function formatLastSeen(date) {
    if (!date) return '';
    const now = new Date();
    const lastSeen = new Date(date);
    const diff = now - lastSeen;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'agora mesmo';
    if (minutes < 60) return `${minutes}m`;
    if (hours < 24) return `${hours}h`;
    return `${days}d`;
}

// Carregar mensagens
function loadChatMessages(chatId) {
    fetch(`php/get_messages.php?chat_id=${chatId}`)
        .then(response => response.json())
        .then(data => {
            messages = data.messages;
            renderMessages();
        })
        .catch(error => {
            console.error('Erro ao carregar mensagens:', error);
            document.getElementById('messages').innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Erro ao carregar mensagens.</p>';
        });
}

// Renderizar mensagens
function renderMessages() {
    const messagesContainer = document.getElementById('messages');
    messagesContainer.innerHTML = '';

    messages.forEach((message, index) => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${message.sent ? 'justify-end' : 'justify-start'} animate-slide-up`;
        messageDiv.style.animationDelay = `${index * 100}ms`;

        const messageContent = createMessageBubble(message);
        
        if (!message.sent) {
            const currentChatData = { avatar: message.avatar || 'https://via.placeholder.com/32' };
            messageDiv.innerHTML = `
                <div class="flex items-end space-x-2 max-w-xs lg:max-w-md">
                    <img src="${currentChatData.avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                    ${messageContent}
                </div>
            `;
        } else {
            messageDiv.innerHTML = `<div class="max-w-xs lg:max-w-md">${messageContent}</div>`;
        }

        messagesContainer.appendChild(messageDiv);
    });

    setTimeout(() => {
        const container = document.getElementById('messagesContainer');
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }, 300);
}

// Criar bolha de mensagem
function createMessageBubble(message) {
    const bubbleClass = message.sent ? 
        'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 
        'bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm text-gray-800 dark:text-gray-100 border border-white/20 dark:border-gray-700/20';
        
    let content = '';
    switch (message.type) {
        case 'file':
            content = `
                <div class="flex items-center space-x-3 p-1">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-pdf text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium">${message.file_name}</p>
                        <p class="text-xs opacity-70">${message.file_size}</p>
                    </div>
                </div>
            `;
            break;
        case 'image':
            content = `<img src="uploads/${message.file_path}" alt="Image" class="max-w-full rounded-lg">`;
            break;
        default:
            content = `<p class="text-sm leading-relaxed">${message.text}</p>`;
    }
    
    const reactions = `
        <div class="message-reactions flex space-x-1 mt-2">
            ${message.reactions && message.reactions.length ? 
                message.reactions.map(r => `<span class="text-xs bg-white/20 dark:bg-gray-700/20 rounded-full px-2 py-1">${r}</span>`).join('') : 
                '<span class="text-xs text-gray-400 dark:text-gray-500">Nenhuma reação</span>'}
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

// Reagir a mensagem
function reactToMessage(messageId, reaction) {
    fetch('php/react_to_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ message_id: messageId, reaction })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const message = messages.find(m => m.id === messageId);
                if (!message.reactions) message.reactions = [];
                if (!message.reactions.includes(reaction)) {
                    message.reactions.push(reaction);
                    renderMessages();
                }
            }
        })
        .catch(error => console.error('Erro ao reagir:', error));
}

// Enviar mensagem
function sendMessage() {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;

    const sendBtn = document.getElementById('sendButton');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('php/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ chat_id: currentChat, message: text, type: 'text' })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                input.value = '';
                updateCharCounter();
                autoResizeTextarea();
                renderChatList();
                loadChatMessages(currentChat);
            } else {
                alert('Erro ao enviar mensagem: ' + result.message);
            }
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        })
        .catch(error => {
            console.error('Erro ao enviar mensagem:', error);
            alert('Erro ao conectar com o servidor');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
}

// Upload de arquivo
function toggleAttachMenu() {
    const attachMenu = document.getElementById('attachMenu');
    attachMenu.classList.toggle('hidden');

    if (!attachMenu.querySelector('input[type="file"]')) {
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*,application/pdf';
        fileInput.className = 'hidden';
        fileInput.onchange = (e) => uploadFile(e.target.files[0]);
        attachMenu.appendChild(fileInput);
        attachMenu.querySelector('button').onclick = () => fileInput.click();
    }
}

function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('chat_id', currentChat);

    fetch('php/upload_file.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadChatMessages(currentChat);
                renderChatList();
            } else {
                alert('Erro ao enviar arquivo: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Erro ao enviar arquivo:', error);
            alert('Erro ao conectar com o servidor');
        });
}

// Polling para atualizações
function startPolling() {
    pollingInterval = setInterval(() => {
        if (currentChat) {
            loadChatMessages(currentChat);
            renderChatList();
        }
    }, 5000);
}

// Demais funções permanecem iguais, com ajustes para backend onde necessário
// Exemplo: createGroup
function createGroup() {
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

    fetch('php/create_group.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ name, description, members: JSON.stringify(selectedMembers) })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                renderChatList();
                closeCreateGroupModal();
                selectChat({ id: result.group_id, name, avatar: 'https://via.placeholder.com/48', is_group: true });
            } else {
                alert('Erro ao criar grupo: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Erro ao criar grupo:', error);
            alert('Erro ao conectar com o servidor');
        });
}

// Configurar eventos
function setupEventListeners() {
    const messageInput = document.getElementById('messageInput');
    
    messageInput.addEventListener('input', () => {
        updateCharCounter();
        autoResizeTextarea();
    });

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    window.addEventListener('resize', handleResize);

    document.addEventListener('click', (e) => {
        const attachMenu = document.getElementById('attachMenu');
        if (!e.target.closest('[onclick="toggleAttachMenu()"]') && !attachMenu.contains(e.target)) {
            attachMenu.classList.add('hidden');
        }
    });
}

// Iniciar
document.addEventListener('DOMContentLoaded', init);