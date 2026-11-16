// Pequenas interações do painel. As confirmações críticas também são validadas no servidor.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!confirm(form.dataset.confirm)) event.preventDefault();
    });
  });
});

/* ==========================================
   MODO CLARO E ESCURO
   ========================================== */

document.addEventListener("DOMContentLoaded", function () {

    const botao = document.getElementById("alternar-tema");

    // Algumas páginas podem não ter o botão.
    if (!botao) {
        return;
    }

    function atualizarBotao() {
        const temaAtual =
            document.documentElement.getAttribute("data-theme");

        const escuro = temaAtual === "dark";

        const icone = botao.querySelector(".theme-icon");
        const texto = botao.querySelector(".theme-label");

        if (icone) {
            icone.textContent = escuro ? "☀" : "☾";
        }

        if (texto) {
            texto.textContent = escuro
                ? "Modo claro"
                : "Modo escuro";
        }

        botao.setAttribute(
            "aria-label",
            escuro ? "Ativar modo claro" : "Ativar modo escuro"
        );

        botao.setAttribute(
            "aria-pressed",
            String(escuro)
        );
    }

    // Mostra o estado atual do tema.
    atualizarBotao();

    // Alterna o tema ao clicar.
    botao.addEventListener("click", function () {

        const temaAtual =
            document.documentElement.getAttribute("data-theme");

        const novoTema =
            temaAtual === "dark" ? "light" : "dark";

        document.documentElement.setAttribute(
            "data-theme",
            novoTema
        );

        // Salva a preferência no navegador.
        try {
            localStorage.setItem("biblioteca-tema", novoTema);
        } catch (erro) {
            console.warn("Não foi possível salvar o tema.", erro);
        }

        atualizarBotao();
    });
});