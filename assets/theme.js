// Theme Management
class ThemeManager {
  constructor() {
    this.theme = localStorage.getItem('theme') || 'light';
    this.init();
  }

  init() {
    this.applyTheme();
    this.bindEvents();
  }

  applyTheme() {
    document.documentElement.setAttribute('data-theme', this.theme);
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
      themeIcon.textContent = this.theme === 'dark' ? '🌙' : '☀️';
    }
  }

  toggleTheme() {
    this.theme = this.theme === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', this.theme);
    this.applyTheme();
  }

  bindEvents() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', () => this.toggleTheme());
    }
  }
}

// Slideshow Management
class SlideshowManager {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.slides = [];
    this.currentSlide = 0;
    this.interval = null;
    this.init();
  }

  init() {
    if (!this.container) return;
    
    this.slides = this.container.querySelectorAll('.slide');
    this.createIndicators();
    this.bindEvents();
    this.startSlideshow();
  }

  createIndicators() {
    const indicatorsContainer = this.container.querySelector('.slide-indicators');
    if (!indicatorsContainer) return;

    indicatorsContainer.innerHTML = '';
    this.slides.forEach((_, index) => {
      const indicator = document.createElement('div');
      indicator.className = 'indicator';
      if (index === 0) indicator.classList.add('active');
      indicator.addEventListener('click', () => this.goToSlide(index));
      indicatorsContainer.appendChild(indicator);
    });
  }

  bindEvents() {
    // Auto-advance slides
    this.container.addEventListener('mouseenter', () => this.stopSlideshow());
    this.container.addEventListener('mouseleave', () => this.startSlideshow());
  }

  startSlideshow() {
    this.interval = setInterval(() => {
      this.nextSlide();
    }, 5000);
  }

  stopSlideshow() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  }

  goToSlide(index) {
    this.slides[this.currentSlide].classList.remove('active');
    this.slides[index].classList.add('active');
    
    const indicators = this.container.querySelectorAll('.indicator');
    indicators[this.currentSlide].classList.remove('active');
    indicators[index].classList.add('active');
    
    this.currentSlide = index;
  }

  nextSlide() {
    const nextIndex = (this.currentSlide + 1) % this.slides.length;
    this.goToSlide(nextIndex);
  }
}

// AI Chatbot
class Chatbot {
  constructor() {
    this.isOpen = false;
    this.messages = [];
    this.mode = (document.body && document.body.getAttribute('data-bot-mode')) || 'client';
    this.historyKey = 'fa_chat_history';
    this.currentConversationId = this.generateId();
    this.init();
  }

  generateId() {
    return 'c_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
  }

  init() {
    this.createChatbot();
    this.bindEvents();
    this.addWelcomeMessage();
  }

  createChatbot() {
    const chatbotHTML = `
      <div class="chatbot-container">
        <button class="chatbot-toggle" id="chatbot-toggle">🤖</button>
        <div class="chatbot-window" id="chatbot-window">
          <div class="chatbot-header">
            <div>
              <h3>FoodieAds Assistant</h3>
              <p class="text-sm opacity-90">How can I help you today?</p>
            </div>
          </div>
          <div class="chatbot-messages" id="chatbot-messages"></div>
          <div class="chatbot-input">
            <input type="text" id="chatbot-input" placeholder="Ask me anything...">
            <input type="file" id="chatbot-image" accept="image/*" style="max-width:140px">
            <button id="chatbot-send">Send</button>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
  }

  bindEvents() {
    const toggle = document.getElementById('chatbot-toggle');
    const send = document.getElementById('chatbot-send');
    const input = document.getElementById('chatbot-input');

    if (toggle) toggle.addEventListener('click', () => this.toggle());
    if (send) send.addEventListener('click', () => this.sendMessage());
    if (input) input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') this.sendMessage();
    });
  }

  toggle() {
    this.isOpen = !this.isOpen;
    const window = document.getElementById('chatbot-window');
    window.style.display = this.isOpen ? 'flex' : 'none';
  }

  close() {
    this.isOpen = false;
    const window = document.getElementById('chatbot-window');
    window.style.display = 'none';
    const panel = document.getElementById('chatbot-history-panel');
    if (panel) panel.style.display = 'none';
  }

  addWelcomeMessage() {
    this.addMessage('bot', this.mode === 'server'
      ? 'Admin assistant ready. Type a command or upload an image.'
      : 'Hello! I\'m your FoodieAds assistant. Ask anything—word/recipe/verse of the day, food tips, or navigation help.');
  }

  addMessage(sender, text) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;
    messageDiv.textContent = text;
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    // record
    this.messages.push({ role: sender === 'user' ? 'user' : 'assistant', content: text });
    this.persistHistory();
  }

  addImagePreview(file) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const wrapper = document.createElement('div');
    wrapper.className = 'message user';
    const img = document.createElement('img');
    img.style.maxWidth = '200px';
    img.style.borderRadius = '8px';
    img.alt = file.name;
    const reader = new FileReader();
    reader.onload = () => { img.src = reader.result; };
    reader.readAsDataURL(file);
    wrapper.appendChild(img);
    messagesContainer.appendChild(wrapper);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  messagesForServer() {
    const system = {
      role: 'system',
      content: "You are FoodieAds' helpful homepage assistant. Be lively, friendly, succinct. Support: word/recipe/verse of the day, food Q&A, navigation help. Offer brief suggestions and follow-up buttons when helpful."
    };
    const last = this.messages.slice(-10); // cap context
    return [system, ...last];
  }

  persistHistory() {
    try {
      const all = JSON.parse(localStorage.getItem(this.historyKey) || '[]');
      const title = (this.messages.find(m => m.role === 'user')?.content || 'New chat').slice(0, 50);
      const idx = all.findIndex(c => c.id === this.currentConversationId);
      const convo = { id: this.currentConversationId, title, messages: this.messages, ts: Date.now() };
      if (idx >= 0) all[idx] = convo; else all.unshift(convo);
      localStorage.setItem(this.historyKey, JSON.stringify(all.slice(0, 50)));
    } catch {}
  }

  newChat() {
    this.currentConversationId = this.generateId();
    this.messages = [];
    const container = document.getElementById('chatbot-messages');
    container.innerHTML = '';
    this.addWelcomeMessage();
  }

  toggleHistory() {
    const panel = document.getElementById('chatbot-history-panel');
    if (!panel) return;
    if (panel.style.display === 'none' || panel.style.display === '') {
      try {
        const all = JSON.parse(localStorage.getItem(this.historyKey) || '[]');
        panel.innerHTML = all.length ? '' : '<div class="text-slate-600">No history yet</div>';
        all.forEach(c => {
          const item = document.createElement('div');
          item.style.padding = '.5rem';
          item.style.borderBottom = '1px solid var(--border-color)';
          item.style.cursor = 'pointer';
          item.textContent = new Date(c.ts).toLocaleString() + ' — ' + c.title;
          item.addEventListener('click', () => {
            this.currentConversationId = c.id;
            this.messages = c.messages || [];
            const container = document.getElementById('chatbot-messages');
            container.innerHTML = '';
            this.messages.forEach(m => this.addMessage(m.role === 'user' ? 'user' : 'bot', m.content));
            panel.style.display = 'none';
          });
          panel.appendChild(item);
        });
      } catch { panel.innerHTML = '<div class="text-slate-600">No history</div>'; }
      panel.style.display = 'block';
    } else {
      panel.style.display = 'none';
    }
  }

  sendMessage() {
    const input = document.getElementById('chatbot-input');
    const fileInput = document.getElementById('chatbot-image');
    const message = input.value.trim();
    const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
    
    if (!message && !file) return;

    if (message) this.addMessage('user', message);
    if (file) this.addImagePreview(file);

    input.value = '';
    if (fileInput) fileInput.value = '';

    // Admin console path stays as-is
    if (this.mode === 'server') {
      const form = new FormData();
      if (message) form.append('command', message);
      if (file) form.append('image', file, file.name);
      fetch('portal_ai_console_action.php', { method: 'POST', body: form })
        .then(res => res.text())
        .then(html => {
          const tmp = document.createElement('div');
          tmp.innerHTML = html;
          const text = tmp.textContent.trim() || 'Done.';
          this.addMessage('bot', text);
        })
        .catch(err => {
          this.addMessage('bot', 'Error: ' + (err && err.message ? err.message : 'Request failed'));
        });
      return;
    }

    // Homepage: prefer OpenAI proxy; fallback to local guidance
    const hasImage = !!file;
    if (hasImage) {
      const form = new FormData();
      form.append('message', message || '');
      if (file) form.append('image', file, file.name);
      fetch('chat_home_assistant.php', { method: 'POST', body: form })
        .then(res => res.ok ? res.text() : Promise.reject(new Error('HTTP ' + res.status)))
        .then(text => { this.addMessage('bot', text.trim()); })
        .catch(() => {
          const response = this.generateResponse(message || 'image');
          this.addMessage('bot', response);
        });
      return;
    }

    const payload = { messages: this.messagesForServer() };
    fetch('chat_home_assistant.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(res => res.ok ? res.text() : Promise.reject(new Error('HTTP ' + res.status)))
      .then(text => {
        this.addMessage('bot', text.trim());
      })
      .catch(() => {
        const response = this.generateResponse(message || (file ? 'image' : ''));
        this.addMessage('bot', response);
      });
  }

  generateResponse(message) {
    const lowerMessage = (message || '').toLowerCase();
    
    if (lowerMessage.includes('word of the day')) {
      return 'Word of the day: Serendipity — finding valuable things not sought for.';
    }
    if (lowerMessage.includes('recipe of the day')) {
      return 'Recipe of the day: Garlic butter shrimp. Sauté shrimp in butter, garlic, lemon, parsley. Serve over pasta.';
    }
    if (lowerMessage.includes('bible verse')) {
      return 'Bible verse of the day: “The Lord is my shepherd; I shall not want.” — Psalm 23:1';
    }

    if (lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('plan')) {
      return 'We offer Basic, Standard, and Premium plans. Want a quick comparison?';
    }
    if (lowerMessage.includes('register') || lowerMessage.includes('sign up') || lowerMessage.includes('join')) {
      return 'To register, click Register in the top bar. It only takes a few minutes!';
    }
    if (lowerMessage.includes('delivery') || lowerMessage.includes('deliver')) {
      return 'We partner with Glovo, Bolt Food, Uber Eats, and InstaPilau for delivery integrations.';
    }
    if (lowerMessage.includes('dashboard') || lowerMessage.includes('account') || lowerMessage.includes('login')) {
      return 'Use the top navigation to Login or open your Dashboard to manage listings.';
    }
    if (lowerMessage.includes('help') || lowerMessage.includes('support')) {
      return 'Happy to help! Ask a question or use the Contact page for support.';
    }
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
      return 'Hello! What would you like to explore today?';
    }
    if (lowerMessage.includes('image') || lowerMessage.includes('photo') || lowerMessage.includes('picture')) {
      return 'Image received. In homepage mode, images are previewed; admin console can store them.';
    }
    
    return 'Got it! Ask for Word/Recipe/Verse of the day, food tips, or site guidance.';
  }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new ThemeManager();
  
  // Initialize slideshow if container exists
  const slideshowContainer = document.getElementById('hero-slideshow');
  if (slideshowContainer) {
    new SlideshowManager('hero-slideshow');
  }
  
  // Initialize chatbot only if opted-in on this page
  const body = document.body;
  if (body && body.getAttribute('data-enable-chatbot') === 'true') {
    new Chatbot();
  }
});

