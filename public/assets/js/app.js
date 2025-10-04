document.addEventListener('DOMContentLoaded', () => {
  // Exemplos simples para dashboard
  const baseUrl = document.querySelector('link[rel=stylesheet][href*="/assets/css/"]').href.split('/assets/')[0];
  // Fetch próximos atendimentos e estoque crítico (endpoints a implementar futuramente)
  
  // Aplicar máscara de data (dd/mm/aaaa) nos campos de admissão
  applyDateMask();
  
  // Aplicar máscaras de telefone e CPF
  applyPhoneMasks();
  applyCpfMask();
});

// Função para aplicar máscara de data dd/mm/aaaa
function applyDateMask() {
  const dateInputs = document.querySelectorAll('input[name="admission_date"], input[name="birth_date"]');
  
  dateInputs.forEach(input => {
    // Flag para evitar loop infinito na validação
    let isValidating = false;
    
    input.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
      
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
      }
      if (value.length >= 5) {
        value = value.substring(0, 5) + '/' + value.substring(5, 9);
      }
      
      e.target.value = value;
    });
    
    // Validação básica ao sair do campo
    input.addEventListener('blur', function(e) {
      // Evita loop infinito
      if (isValidating) return;
      
      const value = e.target.value.trim();
      
      // Se o campo estiver vazio, não valida
      if (!value) return;
      
      const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
      
      // Se não está no formato correto
      if (!datePattern.test(value)) {
        isValidating = true;
        alert('Por favor, digite a data no formato dd/mm/aaaa ou deixe o campo vazio');
        setTimeout(() => {
          e.target.focus();
          e.target.select();
          isValidating = false;
        }, 100);
        return;
      }
      
      // Valida se a data é válida
      const [, day, month, year] = value.match(datePattern);
      const date = new Date(year, month - 1, day);
      
      if (date.getDate() != day || date.getMonth() != month - 1 || date.getFullYear() != year) {
        isValidating = true;
        alert('Data inválida. Por favor, verifique o dia, mês e ano.');
        setTimeout(() => {
          e.target.focus();
          e.target.select();
          isValidating = false;
        }, 100);
      }
    });
  });
}

// Função para aplicar máscaras de telefone
function applyPhoneMasks() {
  // Máscara para telefone celular (xx) xxxxx-xxxx
  const mobileInputs = document.querySelectorAll('input[name="phone"]');
  mobileInputs.forEach(input => {
    input.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
      
      if (value.length >= 2) {
        value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
      }
      if (value.length >= 10) {
        value = value.substring(0, 10) + '-' + value.substring(10, 14);
      }
      
      e.target.value = value;
    });
  });
  
  // Máscara para telefone fixo (xx) xxxx-xxxx
  const landlineInputs = document.querySelectorAll('input[name="landline_phone"]');
  landlineInputs.forEach(input => {
    input.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
      
      if (value.length >= 2) {
        value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
      }
      if (value.length >= 9) {
        value = value.substring(0, 9) + '-' + value.substring(9, 13);
      }
      
      e.target.value = value;
    });
  });
}

// Função para aplicar máscara de CPF
function applyCpfMask() {
  const cpfInputs = document.querySelectorAll('input[name="cpf_cnpj"]');
  
  cpfInputs.forEach(input => {
    input.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
      
      // Se tem mais de 11 dígitos, assume que é CNPJ e não aplica máscara de CPF
      if (value.length <= 11) {
        // Máscara de CPF: xxx.xxx.xxx-xx
        if (value.length >= 3) {
          value = value.substring(0, 3) + '.' + value.substring(3);
        }
        if (value.length >= 7) {
          value = value.substring(0, 7) + '.' + value.substring(7);
        }
        if (value.length >= 11) {
          value = value.substring(0, 11) + '-' + value.substring(11, 13);
        }
      } else {
        // Para CNPJ, mantém apenas os dígitos (pode implementar máscara de CNPJ futuramente)
        value = value.substring(0, 14);
      }
      
      e.target.value = value;
    });
  });
}
