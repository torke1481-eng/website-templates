/**
 * CHATBOT FLOTANTE MODULAR - JAVASCRIPT
 * Sistema de chat con FAQs y respuestas automáticas
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del DOM
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const closeChatbot = document.getElementById('closeChatbot');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendMessage');
    const messagesContainer = document.getElementById('messagesContainer');
    const faqItems = document.querySelectorAll('.faq-item');
    
    let isOpen = false;
    
    // RESPUESTAS AUTOMÁTICAS - Personalizable por Make.com
    const autoResponses = window.CHATBOT_RESPONSES || {
        'hola': '¡Hola! ¿En qué puedo ayudarte?',
        'precio': 'Nuestros precios varían según el producto. ¿Qué te interesa?',
        'envío': 'Hacemos envíos a todo el país.',
        'pago': 'Aceptamos tarjetas, transferencia y efectivo.',
        'gracias': '¡De nada! ¿Algo más?',
        'adiós': '¡Hasta pronto!'
    };
    
    // Toggle chatbot
    function toggleChatbot() {
        isOpen = !isOpen;
        if (isOpen) {
            chatbotWindow.classList.add('open');
            chatbotWindow.classList.remove('closing');
            const notificationDot = document.querySelector('.notification-dot');
            if (notificationDot) notificationDot.style.display = 'none';
            setTimeout(() => chatInput.focus(), 300);
        } else {
            chatbotWindow.classList.add('closing');
            setTimeout(() => {
                chatbotWindow.classList.remove('open', 'closing');
            }, 300);
        }
    }
    
    // Event listeners
    if (chatbotToggle) chatbotToggle.addEventListener('click', toggleChatbot);
    if (closeChatbot) closeChatbot.addEventListener('click', toggleChatbot);
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) toggleChatbot();
    });
    
    // FAQ expandibles
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', function() {
                const isExpanded = item.classList.contains('expanded');
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) otherItem.classList.remove('expanded');
                });
                item.classList.toggle('expanded', !isExpanded);
            });
        }
    });
    
    // Enviar mensaje
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        addUserMessage(message);
        chatInput.value = '';
        
        // Respuesta automática
        setTimeout(() => {
            const response = getAutoResponse(message);
            addBotMessage(response);
        }, 500);
    }
    
    // Agregar mensaje de usuario
    function addUserMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user-message';
        messageDiv.innerHTML = `<div class="message-content">${text}</div>`;
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Agregar mensaje del bot
    function addBotMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = `<div class="message-content">${text}</div>`;
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Obtener respuesta automática
    function getAutoResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        for (const [keyword, response] of Object.entries(autoResponses)) {
            if (lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return 'Gracias por tu mensaje. Un agente te responderá pronto.';
    }
    
    // Scroll al final
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Event listeners de envío
    if (sendButton) sendButton.addEventListener('click', sendMessage);
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
    }
    
    console.log('✅ Chatbot modular cargado');
});
