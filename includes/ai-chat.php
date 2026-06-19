<!-- AI Chat Widget -->
<div class="ai-chat-widget" id="aiChatWidget">
    <!-- Chat Toggle Button -->
    <button class="ai-chat-toggle" id="aiChatToggle" onclick="toggleAIChat()">
        <i class="bi bi-robot"></i>
        <span class="ai-chat-pulse"></span>
    </button>

    <!-- Chat Window -->
    <div class="ai-chat-window" id="aiChatWindow">
        <div class="ai-chat-header">
            <div class="d-flex align-items-center gap-2">
                <div class="ai-chat-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h6 class="mb-0">AI Assistant</h6>
                    <small class="text-muted-custom">Powered by Gemini</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-glass btn-sm" onclick="switchAIMode('shop')" id="modeShop" title="Shopping Assistant">
                    <i class="bi bi-bag"></i>
                </button>
                <button class="btn btn-glass btn-sm" onclick="switchAIMode('gift')" id="modeGift" title="Gift Recommender">
                    <i class="bi bi-gift"></i>
                </button>
                <button class="btn btn-glass btn-sm" onclick="toggleAIChat()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="ai-chat-messages" id="aiChatMessages">
            <div class="ai-message ai-message-bot">
                <div class="ai-message-avatar"><i class="bi bi-robot"></i></div>
                <div class="ai-message-content">
                    <p>Hello! I'm your AI Shopping Assistant for NSBM Marketplace. I can help you:</p>
                    <ul>
                        <li>Find products you need</li>
                        <li>Get personalized recommendations</li>
                        <li>Suggest perfect gifts</li>
                    </ul>
                    <p>How can I help you today?</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="ai-chat-quick" id="aiQuickActions">
            <button class="btn btn-glass btn-sm" onclick="sendQuickMessage('Show me popular products')">Popular Items</button>
            <button class="btn btn-glass btn-sm" onclick="sendQuickMessage('What electronics do you have?')">Electronics</button>
            <button class="btn btn-glass btn-sm" onclick="sendQuickMessage('Suggest a gift under Rs. 5000')">Gift Ideas</button>
            <button class="btn btn-glass btn-sm" onclick="sendQuickMessage('What are the best deals?')">Best Deals</button>
        </div>

        <!-- Chat Input -->
        <div class="ai-chat-input">
            <form onsubmit="sendAIMessage(event)" class="d-flex gap-2">
                <input type="text" id="aiMessageInput" class="form-control form-control-custom" 
                       placeholder="Ask me anything..." autocomplete="off">
                <button type="submit" class="btn btn-primary-custom" id="aiSendBtn">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.ai-chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.ai-chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--gradient-primary);
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(108, 99, 255, 0.4);
    transition: var(--transition);
    position: relative;
}

.ai-chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(108, 99, 255, 0.6);
}

.ai-chat-pulse {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 16px;
    height: 16px;
    background: var(--secondary);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

.ai-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background: rgba(10, 10, 26, 0.98);
    backdrop-filter: blur(30px);
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-lg);
    display: none;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-chat-window.active {
    display: flex;
}

.ai-chat-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border-glass);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(20, 20, 40, 0.9);
}

.ai-chat-avatar {
    width: 35px;
    height: 35px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ai-message {
    display: flex;
    gap: 0.5rem;
    max-width: 90%;
}

.ai-message-bot {
    align-self: flex-start;
}

.ai-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-message-avatar {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.ai-message-bot .ai-message-avatar {
    background: var(--gradient-primary);
    color: white;
}

.ai-message-user .ai-message-avatar {
    background: var(--gradient-secondary);
    color: white;
}

.ai-message-content {
    background: var(--bg-glass);
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    line-height: 1.5;
}

.ai-message-content p:last-child {
    margin-bottom: 0;
}

.ai-message-content ul {
    padding-left: 1.2rem;
    margin-bottom: 0.5rem;
}

.ai-product-suggestions {
    display: grid;
    gap: 0.6rem;
    margin-top: 0.75rem;
}

.ai-product-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.7rem;
    color: var(--text-primary);
    text-decoration: none;
    background: rgba(108, 99, 255, 0.1);
    border: 1px solid rgba(108, 99, 255, 0.3);
    border-radius: var(--radius-sm);
    transition: var(--transition);
}

.ai-product-link:hover {
    color: white;
    border-color: var(--primary);
    transform: translateY(-1px);
}

.ai-product-info {
    min-width: 0;
}

.ai-product-name {
    display: block;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ai-product-price {
    color: var(--secondary);
    font-size: 0.75rem;
}

.ai-message-user .ai-message-content {
    background: rgba(108, 99, 255, 0.15);
    border-color: rgba(108, 99, 255, 0.3);
}

.ai-chat-quick {
    padding: 0.5rem 1rem;
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    border-top: 1px solid var(--border-glass);
}

.ai-chat-quick .btn {
    white-space: nowrap;
    font-size: 0.7rem;
}

.ai-chat-input {
    padding: 1rem;
    border-top: 1px solid var(--border-glass);
    background: rgba(20, 20, 40, 0.9);
}

.ai-chat-input .form-control {
    font-size: 0.85rem;
}

.ai-typing {
    display: flex;
    gap: 4px;
    padding: 0.5rem 0;
}

.ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 100% { transform: translateY(0); opacity: 0.5; }
    50% { transform: translateY(-5px); opacity: 1; }
}

@media (max-width: 576px) {
    .ai-chat-window {
        width: calc(100vw - 20px);
        right: -10px;
        height: calc(100vh - 100px);
        bottom: 70px;
    }
}
</style>

<script>
let aiChatOpen = false;
let aiMode = 'shop'; // 'shop' or 'gift'
let chatContext = [];

function toggleAIChat() {
    aiChatOpen = !aiChatOpen;
    const window = document.getElementById('aiChatWindow');
    const toggle = document.getElementById('aiChatToggle');
    
    if (aiChatOpen) {
        window.classList.add('active');
        toggle.innerHTML = '<i class="bi bi-x-lg"></i>';
        document.getElementById('aiMessageInput').focus();
    } else {
        window.classList.remove('active');
        toggle.innerHTML = '<i class="bi bi-robot"></i><span class="ai-chat-pulse"></span>';
    }
}

function switchAIMode(mode) {
    aiMode = mode;
    document.getElementById('modeShop').classList.toggle('active', mode === 'shop');
    document.getElementById('modeGift').classList.toggle('active', mode === 'gift');
    
    const placeholder = mode === 'gift' 
        ? 'Describe the gift you need...' 
        : 'Ask me anything...';
    document.getElementById('aiMessageInput').placeholder = placeholder;
}

function sendQuickMessage(message) {
    document.getElementById('aiMessageInput').value = message;
    sendAIMessage(new Event('submit'));
}

async function sendAIMessage(e) {
    e.preventDefault();
    
    const input = document.getElementById('aiMessageInput');
    const message = input.value.trim();
    if (!message) return;
    
    // Add user message
    addChatMessage(message, 'user');
    input.value = '';
    // Send only the previous conversation as context. The backend receives the
    // current message separately, so including it here would duplicate it.
    const previousContext = chatContext.slice(-6);
    chatContext.push({ role: 'user', content: message });
    
    // Show typing indicator
    showTyping();
    
    try {
        const endpoint = aiMode === 'gift' ? 'ai.php?action=gift' : 'ai.php?action=chat';
        const body = aiMode === 'gift' 
            ? { request: message }
            : { message: message, context: previousContext };
        
        // The widget is rendered on both the root homepage and pages/ routes.
        // Generate the correct relative API path server-side instead of reading
        // the non-existent API.baseUrl JavaScript property.
        const baseUrl = <?= json_encode(isset($isSubPage) ? '../api/' : 'api/') ?>;
        const response = await fetch(baseUrl + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        
        const result = await response.json();
        removeTyping();

        if (!response.ok) {
            throw new Error(result.message || `AI request failed (${response.status})`);
        }
        
        if (result.success && result.data && result.data.reply) {
            addChatMessage(result.data.reply, 'bot', result.data.products || []);
            chatContext.push({ role: 'assistant', content: result.data.reply });
        } else {
            addChatMessage(result.message || "I'm sorry, I couldn't process that request. Please try again or rephrase your question.", 'bot');
        }
    } catch (error) {
        removeTyping();
        console.error('AI chat request failed:', error);
        addChatMessage("I'm having trouble connecting right now. Please try again in a moment.", 'bot');
    }
}

function escapeChatHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function addChatMessage(content, role, products = []) {
    const messagesDiv = document.getElementById('aiChatMessages');
    const messageEl = document.createElement('div');
    messageEl.className = `ai-message ai-message-${role}`;
    
    // Convert markdown-like formatting
    let formattedContent = escapeChatHtml(content)
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>')
        .replace(/• /g, '&bull; ');
    
    const icon = role === 'bot' ? 'bi-robot' : 'bi-person';
    const productLinks = products.length ? `
        <div class="ai-product-suggestions">
            ${products.map(product => `
                <a class="ai-product-link" href="${escapeChatHtml(product.url)}">
                    <span class="ai-product-info">
                        <span class="ai-product-name">${escapeChatHtml(product.name)}</span>
                        <span class="ai-product-price">${formatPrice(product.price)}</span>
                    </span>
                    <span class="btn btn-primary-custom btn-sm">View Product</span>
                </a>
            `).join('')}
        </div>
    ` : '';
    messageEl.innerHTML = `
        <div class="ai-message-avatar"><i class="bi ${icon}"></i></div>
        <div class="ai-message-content">${formattedContent}${productLinks}</div>
    `;
    
    messagesDiv.appendChild(messageEl);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function showTyping() {
    const messagesDiv = document.getElementById('aiChatMessages');
    const typingEl = document.createElement('div');
    typingEl.className = 'ai-message ai-message-bot';
    typingEl.id = 'aiTyping';
    typingEl.innerHTML = `
        <div class="ai-message-avatar"><i class="bi bi-robot"></i></div>
        <div class="ai-message-content">
            <div class="ai-typing"><span></span><span></span><span></span></div>
        </div>
    `;
    messagesDiv.appendChild(typingEl);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function removeTyping() {
    const typing = document.getElementById('aiTyping');
    if (typing) typing.remove();
}
</script>
