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
            <div class="ai-chat-identity">
                <div class="ai-chat-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="ai-chat-title-wrap">
                    <h6 id="aiChatTitle">AI Shopping Assistant</h6>
                    <small><span class="ai-status-dot"></span>Online · Powered by Gemini</small>
                </div>
            </div>
            <div class="ai-chat-actions">
                <button type="button" class="ai-header-action active" onclick="switchAIMode('shop')" id="modeShop" title="Shopping Assistant" aria-label="Shopping Assistant" aria-pressed="true">
                    <i class="bi bi-bag"></i>
                </button>
                <button type="button" class="ai-header-action" onclick="switchAIMode('gift')" id="modeGift" title="Gift Recommender" aria-label="Gift Recommender" aria-pressed="false">
                    <i class="bi bi-gift"></i>
                </button>
                <button class="ai-header-action" onclick="toggleAIChat()" title="Close assistant" aria-label="Close assistant">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="ai-chat-messages" id="aiChatMessages">
            <div class="ai-message ai-message-bot">
                <div class="ai-message-avatar"><i class="bi bi-robot"></i></div>
                <div class="ai-message-content">
                    <p>Hello! I'm your AI Shopping Assistant for GreenLink Market. I can help you:</p>
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
            <button type="button" class="btn btn-glass btn-sm" data-prompt="Show me useful campus essentials for NSBM university life" onclick="fillQuickMessage(this.dataset.prompt)">Campus Essentials</button>
            <button type="button" class="btn btn-glass btn-sm" data-prompt="Show me the most popular products among university students" onclick="fillQuickMessage(this.dataset.prompt)">Popular Items</button>
            <button type="button" class="btn btn-glass btn-sm" data-prompt="Show me the best student-friendly deals currently available" onclick="fillQuickMessage(this.dataset.prompt)">Student Deals</button>
            <button type="button" class="btn btn-glass btn-sm" data-prompt="Recommend useful study supplies and technology for an NSBM student" onclick="fillQuickMessage(this.dataset.prompt)">Study &amp; Tech</button>
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
    box-shadow: 0 4px 20px rgba(8, 122, 75, 0.4);
    transition: var(--transition);
    position: relative;
}

.ai-chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(8, 122, 75, 0.6);
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
    width: 440px;
    max-width: calc(100vw - 32px);
    height: 640px;
    max-height: calc(100vh - 110px);
    background: rgba(5, 18, 12, 0.98);
    backdrop-filter: blur(30px);
    border: 1px solid rgba(129, 217, 163, 0.28);
    border-radius: var(--radius-lg);
    display: none;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 28px 80px rgba(0, 0, 0, 0.58), 0 0 0 1px rgba(255, 255, 255, 0.025) inset;
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
    min-height: 78px;
    padding: 1rem 1.1rem;
    border-bottom: 1px solid rgba(129, 217, 163, 0.22);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, rgba(16, 45, 30, 0.98), rgba(7, 25, 17, 0.98));
    gap: 0.75rem;
}

.ai-chat-identity {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-width: 0;
}

.ai-chat-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.ai-chat-title-wrap {
    min-width: 0;
}

.ai-chat-title-wrap h6 {
    margin: 0 0 0.2rem;
    color: var(--text-primary);
    font-size: 0.94rem;
    font-weight: 700;
    white-space: nowrap;
}

.ai-chat-title-wrap small {
    display: flex;
    align-items: center;
    color: var(--text-muted);
    font-size: 0.72rem;
    white-space: nowrap;
}

.ai-status-dot {
    width: 7px;
    height: 7px;
    margin-right: 0.35rem;
    background: var(--secondary);
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(182, 227, 74, 0.8);
}

.ai-chat-actions {
    display: flex;
    flex-shrink: 0;
    gap: 0.45rem;
}

.ai-header-action {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    padding: 0;
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.045);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    transition: var(--transition);
}

.ai-header-action:hover {
    color: white;
    background: rgba(8, 122, 75, 0.2);
    border-color: rgba(129, 217, 163, 0.6);
}

.ai-header-action.active {
    color: white;
    background: var(--gradient-primary);
    border-color: rgba(182, 227, 74, 0.85);
    box-shadow: 0 0 0 2px rgba(182, 227, 74, 0.12), 0 6px 18px rgba(8, 122, 75, 0.32);
    transform: translateY(-1px);
}

.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(8, 122, 75, 0.7) transparent;
}

.ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
    background: rgba(8, 122, 75, 0.7);
    border-radius: 10px;
}

.ai-message {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.ai-message-bot {
    align-self: flex-start;
}

.ai-message-user {
    align-self: flex-end;
    flex-direction: row-reverse;
    width: auto;
    max-width: 84%;
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
    flex: 1;
    min-width: 0;
    overflow-wrap: anywhere;
    background: linear-gradient(145deg, rgba(18, 49, 33, 0.94), rgba(9, 29, 19, 0.96));
    border: 1px solid rgba(129, 217, 163, 0.22);
    border-radius: 4px 16px 16px 16px;
    padding: 0.95rem 1rem;
    color: var(--text-secondary);
    font-size: 0.86rem;
    line-height: 1.65;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
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
    gap: 0.7rem;
    margin-top: 1rem;
    padding-top: 0.9rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.ai-product-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    gap: 0.8rem;
    padding: 0.8rem 0.85rem;
    color: var(--text-primary);
    text-decoration: none;
    background: linear-gradient(135deg, rgba(8, 122, 75, 0.14), rgba(182, 227, 74, 0.05));
    border: 1px solid rgba(75, 191, 123, 0.42);
    border-radius: 12px;
    transition: var(--transition);
}

.ai-product-link:hover {
    color: white;
    border-color: var(--primary);
    transform: translateY(-1px);
}

.ai-product-info {
    flex: 1;
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
    display: block;
    margin-top: 0.15rem;
    color: var(--secondary);
    font-size: 0.75rem;
}

.ai-product-action {
    flex-shrink: 0;
    padding: 0.48rem 0.7rem;
    color: white;
    background: var(--gradient-primary);
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.ai-message-user .ai-message-content {
    flex: initial;
    background: linear-gradient(135deg, rgba(8, 122, 75, 0.3), rgba(6, 91, 57, 0.26));
    border-color: rgba(75, 191, 123, 0.58);
    border-radius: 16px 4px 16px 16px;
    color: var(--text-primary);
}

.ai-chat-quick {
    padding: 0.8rem 1rem;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
    overflow: hidden;
    border-top: 1px solid rgba(129, 217, 163, 0.18);
    background: rgba(6, 22, 14, 0.96);
}

.ai-chat-quick .btn {
    width: 100%;
    min-width: 0;
    padding: 0.48rem 0.35rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.68rem;
}

.ai-chat-input {
    padding: 0.9rem 1rem 1rem;
    border-top: 1px solid rgba(129, 217, 163, 0.18);
    background: rgba(9, 29, 19, 0.98);
}

.ai-chat-input form {
    min-width: 0;
}

.ai-chat-input .form-control {
    min-width: 0;
    height: 44px;
    padding-inline: 1rem;
    border-radius: 999px;
    font-size: 0.84rem;
}

.ai-chat-input #aiSendBtn {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    padding: 0;
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
        width: calc(100vw - 24px);
        right: -8px;
        height: min(640px, calc(100vh - 96px));
        bottom: 70px;
    }

    .ai-chat-header {
        min-height: 72px;
        padding: 0.85rem;
    }

    .ai-header-action {
        width: 34px;
        height: 34px;
    }

    .ai-chat-messages {
        padding: 0.85rem;
    }

    .ai-message-avatar {
        display: none;
    }

    .ai-message-user {
        max-width: 90%;
    }
}

@media (max-width: 390px) {
    .ai-chat-title-wrap small {
        max-width: 125px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}
</style>

<script>
let aiChatOpen = false;
let aiMode = 'shop'; // 'shop' or 'gift'
let chatContext = [];
const aiQuickPrompts = {
    shop: [
        ['Campus Essentials', 'Show me useful campus essentials for NSBM university life'],
        ['Popular Items', 'Show me the most popular products among university students'],
        ['Student Deals', 'Show me the best student-friendly deals currently available'],
        ['Study & Tech', 'Recommend useful study supplies and technology for an NSBM student']
    ],
    gift: [
        ["Valentine's Gifts", "Suggest a Valentine's Day gift for my partner under Rs. 5000"],
        ['University Events', 'Suggest a special gift for an NSBM university event'],
        ['Graduation Gifts', 'Recommend a graduation gift for an NSBM student'],
        ['Birthday Gifts', 'Suggest a birthday gift for a university friend under Rs. 5000']
    ]
};

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
    if (mode !== 'shop' && mode !== 'gift') return;

    aiMode = mode;
    const shopButton = document.getElementById('modeShop');
    const giftButton = document.getElementById('modeGift');
    const input = document.getElementById('aiMessageInput');
    const isGiftMode = mode === 'gift';

    shopButton.classList.toggle('active', !isGiftMode);
    giftButton.classList.toggle('active', isGiftMode);
    shopButton.setAttribute('aria-pressed', String(!isGiftMode));
    giftButton.setAttribute('aria-pressed', String(isGiftMode));
    document.getElementById('aiChatTitle').textContent = isGiftMode
        ? 'AI Gift Recommender'
        : 'AI Shopping Assistant';

    document.querySelectorAll('#aiQuickActions button').forEach((button, index) => {
        const [label, prompt] = aiQuickPrompts[mode][index];
        button.textContent = label;
        button.dataset.prompt = prompt;
    });
    
    const placeholder = isGiftMode
        ? 'Describe the gift you need...' 
        : 'Ask me anything...';
    input.placeholder = placeholder;
    input.focus();
}

function fillQuickMessage(message) {
    const input = document.getElementById('aiMessageInput');
    input.value = message;
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
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
                    <span class="ai-product-action">View Product</span>
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
