<?php
/**
 * Admin AI Settings - GreenLink Market
 */
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Review.php';

$currentPage = 'ai-settings';
$pageTitle = 'AI Settings';
$apiKeyConfigured = GEMINI_API_KEY !== '' && GEMINI_API_KEY !== 'your-gemini-api-key-here';

include __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <!-- Gemini API Config -->
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-key me-2"></i>Gemini API Configuration</h5>
            </div>
            <div class="admin-card-body">
                <div class="mb-3">
                    <label class="form-label-custom">API Key</label>
                    <div class="input-group">
                        <input type="password" id="apiKey" class="form-control form-control-custom"
                               value="<?= $apiKeyConfigured ? str_repeat('•', 20) : '' ?>"
                               placeholder="Not configured" readonly>
                        <button class="btn btn-glass" onclick="toggleKeyVisibility()">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted-custom">
                        <?= $apiKeyConfigured
                            ? 'Configured through the GEMINI_API_KEY server environment variable.'
                            : 'Set the GEMINI_API_KEY server environment variable. Get a key from <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-gradient">Google AI Studio</a>.' ?>
                    </small>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Model</label>
                    <select class="form-control form-control-custom" disabled>
                        <option selected><?= sanitize(GEMINI_MODEL) ?> (server configured)</option>
                    </select>
                </div>
                <button class="btn btn-primary-custom btn-sm" onclick="testConnection()">
                    <i class="bi bi-lightning me-1"></i> Test Connection
                </button>
            </div>
        </div>
    </div>

    <!-- AI Features Status -->
    <div class="col-lg-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-robot me-2"></i>AI Features</h5>
            </div>
            <div class="admin-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border-glass);">
                    <div>
                        <strong>Shopping Assistant</strong>
                        <div class="text-muted-custom small">Chat-based product recommendations</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border-glass);">
                    <div>
                        <strong>Gift Recommender</strong>
                        <div class="text-muted-custom small">AI-powered gift suggestions</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Dynamic Hero Generator</strong>
                        <div class="text-muted-custom small">AI-generated marketing content</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Prompts -->
    <div class="col-12">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5><i class="bi bi-chat-text me-2"></i>System Prompts</h5>
            </div>
            <div class="admin-card-body">
                <div class="mb-3">
                    <label class="form-label-custom">Shopping Assistant Prompt</label>
                    <textarea class="form-control form-control-custom" rows="3">You are a helpful shopping assistant for GreenLink Market. Help students find products, compare items, and make purchase decisions. Always recommend products from our available catalog.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Gift Recommender Prompt</label>
                    <textarea class="form-control form-control-custom" rows="3">You are a gift recommendation expert for NSBM students. Analyze user preferences, occasions, and budgets to suggest perfect gifts from our product catalog.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Hero Generator Prompt</label>
                    <textarea class="form-control form-control-custom" rows="3">Generate creative, engaging marketing headlines for GreenLink Market. Focus on campus life, student needs, and premium quality. Keep headlines short and impactful.</textarea>
                </div>
                <button class="btn btn-primary-custom btn-sm">
                    <i class="bi bi-check-lg me-1"></i> Save Prompts
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleKeyVisibility() {
    const input = document.getElementById('apiKey');
    input.type = input.type === 'password' ? 'text' : 'password';
}

async function testConnection() {
    showToast('Testing Gemini API connection...');
    try {
        const response = await fetch('../../api/ai.php?action=test', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: '{}'
        });
        const result = await response.json();
        if (result.success) showToast('Gemini API connected successfully!', 'success');
        else showToast('Connection failed: ' + (result.message || 'Check API key'), 'error');
    } catch (e) {
        showToast('Connection test failed', 'error');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
