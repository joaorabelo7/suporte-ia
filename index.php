<?php
// URL da API Flask. Se mudar a porta ou o endereço, altere só aqui.
$apiUrl = "http://127.0.0.1:5000/api/perguntar";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assistente de Educação Financeira</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
      background: #f3f5f9;
      color: #1f2937;
      display: flex;
      justify-content: center;
      min-height: 100vh;
    }
    .chat {
      width: 100%;
      max-width: 720px;
      display: flex;
      flex-direction: column;
      height: 100vh;
      background: #fff;
      box-shadow: 0 0 24px rgba(0, 0, 0, .08);
    }
    header {
      padding: 16px 20px;
      background: #0f766e;
      color: #fff;
    }
    header h1 { margin: 0; font-size: 1.1rem; }
    header p { margin: 4px 0 0; font-size: .85rem; opacity: .85; }
    #mensagens {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .msg {
      max-width: 85%;
      padding: 10px 14px;
      border-radius: 14px;
      line-height: 1.5;
      word-wrap: break-word;
    }
    .msg.usuario {
      align-self: flex-end;
      background: #0f766e;
      color: #fff;
      border-bottom-right-radius: 4px;
    }
    .msg.ia {
      align-self: flex-start;
      background: #eef2f7;
      border-bottom-left-radius: 4px;
    }
    .msg.erro { background: #fee2e2; color: #991b1b; }
    .msg.ia p:first-child { margin-top: 0; }
    .msg.ia p:last-child { margin-bottom: 0; }
    .msg.ia table { border-collapse: collapse; margin: 8px 0; font-size: .9rem; }
    .msg.ia th, .msg.ia td { border: 1px solid #cbd5e1; padding: 4px 8px; }
    .msg.ia code { background: #dbe3ee; padding: 1px 5px; border-radius: 4px; }
    form {
      display: flex;
      gap: 8px;
      padding: 14px 20px;
      border-top: 1px solid #e5e7eb;
    }
    input[type="text"] {
      flex: 1;
      padding: 12px 14px;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      font-size: 1rem;
    }
    input[type="text"]:focus { outline: 2px solid #0f766e; border-color: transparent; }
    button {
      padding: 0 20px;
      border: 0;
      border-radius: 10px;
      background: #0f766e;
      color: #fff;
      font-size: 1rem;
      cursor: pointer;
    }
    button:disabled { background: #94a3b8; cursor: not-allowed; }
  </style>
</head>
<body>
  <div class="chat">
    <header>
      <h1>Assistente de Educação Financeira</h1>
      <p>Pergunte sobre orçamento, reserva de emergência, juros e investimentos básicos.</p>
    </header>

    <div id="mensagens">
      <div class="msg ia">Olá! Sou o assistente de educação financeira. Como posso ajudar?</div>
    </div>

    <form id="formulario">
      <input type="text" id="pergunta" placeholder="Digite sua pergunta..." maxlength="1000" autocomplete="off" required>
      <button type="submit" id="enviar">Enviar</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
  <script>
    const API_URL = <?= json_encode($apiUrl) ?>;

    const mensagens = document.getElementById("mensagens");
    const formulario = document.getElementById("formulario");
    const campo = document.getElementById("pergunta");
    const botao = document.getElementById("enviar");

    function adicionar(texto, tipo, comoMarkdown = false) {
      const div = document.createElement("div");
      div.className = "msg " + tipo;
      if (comoMarkdown) {
        // Converte Markdown em HTML e sanitiza antes de exibir.
        div.innerHTML = DOMPurify.sanitize(marked.parse(texto));
      } else {
        div.textContent = texto; // textContent evita injeção de HTML
      }
      mensagens.appendChild(div);
      mensagens.scrollTop = mensagens.scrollHeight;
      return div;
    }

    formulario.addEventListener("submit", async (e) => {
      e.preventDefault();
      const pergunta = campo.value.trim();
      if (!pergunta) return;

      adicionar(pergunta, "usuario");
      campo.value = "";
      botao.disabled = true;
      const carregando = adicionar("Pensando...", "ia");

      try {
        const resp = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ pergunta })
        });
        const dados = await resp.json();
        carregando.remove();

        if (!resp.ok) {
          adicionar(dados.erro || "Erro inesperado.", "ia erro");
        } else {
          adicionar(dados.resposta, "ia", true);
        }
      } catch (err) {
        carregando.remove();
        adicionar("Não foi possível conectar à API. Verifique se o servidor Flask está rodando.", "ia erro");
      } finally {
        botao.disabled = false;
        campo.focus();
      }
    });
  </script>
</body>
</html>