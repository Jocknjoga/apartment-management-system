
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
  }

  function toggleChat() {
    const chat = document.getElementById('chatPopup');
    chat.style.display = (chat.style.display === 'flex') ? 'none' : 'flex';
  }

  function sendMessage() {
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    const userText = input.value.trim();

    if (userText) {
      const userMessage = document.createElement('p');
      userMessage.innerHTML = `<strong>You:</strong> ${userText}`;
      messages.appendChild(userMessage);

      // Auto-scroll to bottom
      messages.scrollTop = messages.scrollHeight;

      // Clear input
      input.value = '';

      // Simulate bot reply (you can replace this with actual logic later)
      setTimeout(() => {
        const botReply = document.createElement('p');
        botReply.innerHTML = `<strong>Bot:</strong> Thank you for your message!`;
        messages.appendChild(botReply);
        messages.scrollTop = messages.scrollHeight;
      }, 1000);
    }
}
// notification 

  function toggleMessage(row) {
    row.classList.toggle('expanded');
    row.classList.remove('unread');
  }

function markAsRead(id, row) {
  row.classList.remove('unread');
  row.classList.toggle("open");
  fetch("mark_read.php?id=" + id)
    .then(response => response.text())
    .then(data => console.log(data));
}
