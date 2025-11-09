// JavaScript para funcionalidades da área de administração

document.addEventListener("DOMContentLoaded", function () {
  console.log("Área de Administração carregada");

  inicializarDashboard();
  inicializarGraficos();
});

// Inicializar dashboard
function inicializarDashboard() {
  // Atualizar estatísticas em tempo real se necessário
  console.log("Dashboard inicializado");
}

// Inicializar gráficos
function inicializarGraficos() {
  // Configurações padrão para gráficos podem ser adicionadas aqui
  console.log("Sistema de gráficos inicializado");
}

// Função para exportar dados
function exportarDados(tipo) {
  // Implementar lógica de exportação aqui
  alert(`Funcionalidade de exportação de ${tipo} será implementada aqui.`);
}

// Função para filtrar dados em tabelas
function filtrarTabela(inputId, tabelaId) {
  const input = document.getElementById(inputId);
  const tabela = document.getElementById(tabelaId);
  const linhas = tabela.getElementsByTagName("tr");
  const filtro = input.value.toLowerCase();

  for (let i = 0; i < linhas.length; i++) {
    const celulas = linhas[i].getElementsByTagName("td");
    let encontrado = false;

    for (let j = 0; j < celulas.length; j++) {
      const celula = celulas[j];
      if (celula) {
        if (celula.textContent.toLowerCase().indexOf(filtro) > -1) {
          encontrado = true;
          break;
        }
      }
    }

    linhas[i].style.display = encontrado ? "" : "none";
  }
}

// Função para ordenar tabelas
function ordenarTabela(tabelaId, coluna) {
  const tabela = document.getElementById(tabelaId);
  const linhas = Array.from(tabela.getElementsByTagName("tr"));
  const cabecalho = linhas.shift(); // Remove cabeçalho

  const direcao =
    tabela.getAttribute("data-sort-direction") === "asc" ? "desc" : "asc";
  tabela.setAttribute("data-sort-direction", direcao);

  linhas.sort((a, b) => {
    const valorA = a.getElementsByTagName("td")[coluna].textContent;
    const valorB = b.getElementsByTagName("td")[coluna].textContent;

    if (direcao === "asc") {
      return valorA.localeCompare(valorB);
    } else {
      return valorB.localeCompare(valorA);
    }
  });

  // Reconstruir tabela
  tabela.innerHTML = "";
  tabela.appendChild(cabecalho);
  linhas.forEach((linha) => tabela.appendChild(linha));
}

// Função para confirmar ações destrutivas
function confirmarAcao(mensagem) {
  return confirm(mensagem);
}
