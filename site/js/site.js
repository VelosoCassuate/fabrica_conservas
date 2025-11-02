// JavaScript para funcionalidades do site

// Inicialização quando o documento estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    console.log('Site da Fábrica de Conservas carregado');
    
    // Adicionar funcionalidades específicas aqui
    inicializarFormularios();
    inicializarInteratividade();
});

// Inicializar validações de formulários
function inicializarFormularios() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Validação básica pode ser adicionada aqui
            console.log('Formulário submetido:', this.id || 'formulário sem ID');
        });
    });
}

// Inicializar elementos interativos
function inicializarInteratividade() {
    // Adicionar tooltips se necessário
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', mostrarTooltip);
        element.addEventListener('mouseleave', esconderTooltip);
    });
}

// Funções de tooltip
function mostrarTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.position = 'absolute';
    tooltip.style.background = 'rgba(0,0,0,0.8)';
    tooltip.style.color = 'white';
    tooltip.style.padding = '5px 10px';
    tooltip.style.borderRadius = '3px';
    tooltip.style.zIndex = '1000';
    
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    
    this.tooltipElement = tooltip;
}

function esconderTooltip() {
    if (this.tooltipElement) {
        this.tooltipElement.remove();
        this.tooltipElement = null;
    }
}

// Função para formatação de moeda
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(valor);
}

// Função para mostrar mensagens de sucesso/erro
function mostrarMensagem(mensagem, tipo = 'info') {
    const mensagemDiv = document.createElement('div');
    mensagemDiv.className = `mensagem ${tipo}`;
    mensagemDiv.textContent = mensagem;
    
    document.body.insertBefore(mensagemDiv, document.body.firstChild);
    
    setTimeout(() => {
        mensagemDiv.remove();
    }, 5000);
}