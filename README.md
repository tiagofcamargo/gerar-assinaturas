# Gerador de Assinaturas de E-mail - CSINFRA

![PHP](https://img.shields.io/badge/PHP-7.4+-8892BF.svg?logo=php&logoColor=white)
![Status](https://img.shields.io/badge/status-em%20uso-brightgreen)
![License](https://img.shields.io/badge/license-CSInfra-informational)
![Responsive](https://img.shields.io/badge/interface-responsiva-blue)

Este projeto é uma aplicação PHP simples que permite aos colaboradores selecionarem sua empresa, preencherem seus dados pessoais e gerar automaticamente uma imagem de assinatura de e-mail personalizada.

> 🔗 Acesse em produção: [csinfra.com.br/assinaturas](https://csinfra.com.br/assinaturas)

---

## ✨ Funcionalidades

- Escolha da empresa por logotipo
- Preenchimento de dados pessoais: Nome, Cargo, E-mail e Telefone
- Geração de imagem de assinatura pronta para uso
- Estilo visual personalizado para cada empresa (logo, cor, fonte)
- Visualização prévia da base da assinatura

---

## 🧱 Estrutura do Projeto

```
/assinaturas
├── assets/                  # Logos e imagens base das empresas
├── fonts/                   # Fontes utilizadas na imagem gerada
│   └── intelo/              # Pasta com variantes da fonte Intelo
├── index.php                # Página inicial (UI e formulário)
├── gerar.php                # Geração dinâmica da assinatura (imagem)
└── empresas.php             # Configurações de cada empresa
```

---

## ⚙️ Tecnologias Utilizadas

- **PHP (puro)**: lógica de backend e geração de imagem com GD
- **GD Library**: criação e edição de imagens dinamicamente
- **HTML/CSS**: interface com modal e estilização moderna
- **JavaScript**: controle dos modais

---

## 🖼️ Como Funciona

1. O usuário acessa `/assinaturas`
2. Seleciona o logo da empresa desejada
3. Um modal com formulário se abre
4. Ao preencher e enviar, um `POST` é enviado para `gerar.php`
5. O script gera uma imagem PNG com os dados inseridos sobre uma imagem base da empresa

---

## 🏢 Empresas Configuradas

As empresas e suas configurações visuais estão no arquivo `empresas.php`. Cada empresa contém:

```php
'nome' => 'Nome exibido',
'logo' => 'Caminho para o botão com logo',
'base' => 'Imagem base onde os dados são sobrepostos',
'fonte' => 'Fonte usada para texto',
'cor' => 'Cor principal (nome)',
'cor_telefone' => 'Cor usada para o telefone'
```

---

## 📌 Pré-requisitos

- PHP 7.4 ou superior
- Extensão GD habilitada
- Servidor que aceite execução de scripts PHP (Apache, Nginx, etc.)

---

## 🚀 Como rodar localmente

1. Clone este repositório:

   ```bash
   git clone https://github.com/tiagofcamargo/gerar-assinaturas.git
   ```

2. Coloque-o em um servidor local com PHP (como XAMPP ou MAMP).

3. Acesse `http://localhost/gerador-assinaturas/index.php`

---

## 🔐 Observações de segurança

- Os dados são processados diretamente via POST e exibidos como imagem – **nenhuma informação é armazenada**.
- Certifique-se de validar as entradas se adaptar isso para ambientes mais críticos.

---

## 📄 Licença

Este projeto é livre para uso interno da CS Infra. Para uso externo, consulte os responsáveis pela TI.

---

## 🙌 Contribuições

Este projeto foi desenvolvido para facilitar a padronização de assinaturas de e-mail entre empresas do grupo. Sugestões e melhorias são bem-vindas.
