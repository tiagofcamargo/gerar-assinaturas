# Gerador de Assinaturas de E-mail – Grupo CS

Aplicação web desenvolvida em **PHP** para geração automática de **assinaturas de e-mail corporativas**, personalizadas por empresa, com **QR Code vCard embutido**.

A ferramenta permite que colaboradores selecionem sua empresa, informem seus dados e obtenham uma **imagem PNG pronta para uso** em clientes de e-mail como Outlook, Gmail e Apple Mail.

🔗 **Produção**: https://csinfra.com.br/assinaturas

---

## 📌 Visão Geral

O sistema foi projetado para ser:

- Simples de usar (interface intuitiva por empresa)
- Seguro (nenhum dado é persistido)
- Padronizado (layout, cores e fontes por empresa)
- Autossuficiente (não depende de banco de dados)
- Escalável (adição de novas empresas sem alterar a lógica central)

---

## ✨ Funcionalidades

- Seleção da empresa por logotipo
- Preenchimento de dados pessoais:
  - Primeiro nome
  - Sobrenome
  - Cargo
  - E-mail corporativo
  - Telefone
- Validação de domínio de e-mail por empresa
- Geração automática de:
  - Imagem PNG da assinatura
  - QR Code com vCard (nome, empresa, cargo, telefone, e-mail, site e endereço)
- Layout e identidade visual específicos por empresa
- Suporte a múltiplos idiomas/países (Mercovia – BR / AR)
- Visualização prévia da assinatura antes do download
- Download direto do arquivo PNG

> ⚠️ **Importante**: Nenhuma informação é salva em banco de dados ou arquivos. Todos os dados são usados apenas em memória durante a geração da assinatura.

---

## 🧱 Estrutura do Projeto

```
/gerar-assinaturas
├── assets/               # Logos e imagens base das assinaturas
├── fonts/                # Fontes utilizadas (TTF / TTC)
├── css/                  # Estilos da aplicação
├── js/                   # JavaScript (interações, validações e AJAX)
│   └── main.js
├── index.php             # Interface principal
├── gerar.php             # Backend de geração da assinatura (PNG + QR)
├── empresas.php          # Configuração das empresas
├── vendor/               # Dependências do Composer
├── composer.json
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## 🗂️ empresas.php

Arquivo central de configuração das empresas.

Cada empresa define:

- Nome
- Logo
- Imagem base da assinatura
- Fonte
- Cores
- Endereço
- Site
- Domínios de e-mail permitidos

Exemplo:

```php
'empresa_x' => [
  'nome' => 'Empresa X',
  'logo' => 'assets/empresa_x/btn.png',
  'base' => 'assets/empresa_x/base.png',
  'fonte' => './fonts/intelo/Intelo-Regular.ttf',
  'cor' => '#123456',
  'cor_telefone' => '#654321',
  'endereco' => 'Endereço completo',
  'site' => 'https://empresa.com.br/',
  'dominios_email' => ['empresa.com.br'],
]
```

---

## 🔐 Validações de Segurança

### ✔️ Frontend (JavaScript)

- Validação de domínio do e-mail antes do envio
- Bloqueio de e-mails públicos (gmail, hotmail, etc.)
- Mensagens de erro contextualizadas por idioma

### ✔️ Backend (PHP)

- Validação obrigatória de todos os campos
- Validação de e-mail com `FILTER_VALIDATE_EMAIL`
- Validação de domínio permitida por empresa
- Normalização de telefone para padrão **E.164**
- Escape de caracteres especiais no vCard
- Fallbacks de fonte e cores

> Mesmo que o JavaScript seja burlado, o backend impede a geração indevida.

---

## 📱 QR Code vCard

Cada assinatura contém um QR Code que gera um **vCard 3.0**, compatível com:

- Android
- iOS
- Outlook
- Google Contacts
- Apple Contacts

Campos incluídos:

- Nome completo
- Empresa
- Cargo
- Telefone
- E-mail
- Endereço
- Site

---

## 🌍 Internacionalização (Mercovia)

A empresa **CS Rodovias Mercosul** possui:

- Etapa extra de seleção de país (Brasil / Argentina)
- Tradução dinâmica de labels e mensagens
- Máscara de telefone específica por país
- vCard gerado corretamente conforme país selecionado

---

## ⚙️ Tecnologias Utilizadas

- PHP (puro)
- GD Library
- endroid/qr-code
- HTML5
- CSS3
- JavaScript (Vanilla)
- Docker / Docker Compose

---

## 🚀 Como Rodar Localmente (Docker)

### Pré-requisitos
- Docker
- Docker Compose

### Subir o ambiente

```bash
docker compose up --build
```

Acesse no navegador:

```
http://localhost:8080
```

---

## 🛡️ Privacidade

- Nenhum dado é persistido
- Nenhum log de informações pessoais
- Nenhuma dependência externa de rastreamento

A aplicação opera **100% stateless**.

---

## 🧩 Manutenção e Evolução

O projeto foi estruturado para permitir:

- Inclusão de novas empresas apenas via `empresas.php`
- Ajustes visuais sem impacto na lógica
- Evolução para HTML Signature ou PDF, se necessário
- Integração futura com autenticação corporativa

---

## 📄 Licença

Uso interno do **Grupo CS**.  
Distribuição externa não autorizada.
