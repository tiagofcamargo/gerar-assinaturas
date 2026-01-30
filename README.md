# Gerador de Assinaturas de E-mail - CSINFRA

\\

Este projeto é uma aplicação PHP simples que permite aos colaboradores selecionarem sua empresa, preencherem seus dados pessoais e gerar automaticamente uma imagem de assinatura de e-mail personalizada **com QR Code vCard** embutido.

> 🔗 Acesse em produção: [csinfra.com.br/assinaturas](https://csinfra.com.br/assinaturas)

---

## ✨ Funcionalidades

- Escolha da empresa por logotipo
- Preenchimento de dados pessoais: Primeiro Nome, Sobrenome, Cargo, E-mail e Telefone
- Geração de imagem de assinatura pronta para uso (PNG com fundo branco)
- QR Code contendo o vCard gerado dinamicamente a partir dos dados do usuário
- Estilo visual personalizado para cada empresa (logo, cor, fonte)
- Visualização prévia da base da assinatura

> **Observação**: Os dados inseridos **não são salvos em nenhum banco de dados ou arquivo** — são descartados logo após a geração da assinatura. O uso das informações geradas é de inteira responsabilidade do usuário.

---

## 🧱 Estrutura do Projeto

```
/assinaturas
├── assets/                  # Logos e imagens base das empresas (PNG sem QR estático)
├── fonts/                   # Fontes utilizadas na imagem gerada
│   └── intelo/              # Pasta com variantes da fonte Intelo
├── index.php                # Página inicial (UI e formulário)
├── gerar.php                # Geração dinâmica da assinatura (PNG com fundo branco + QR Code vCard)
├── empresas.php             # Configurações de cada empresa
├── vendor/                  # Dependências instaladas pelo Composer (endroid/qr-code)
└── composer.json            # Declaração das dependências (endroid/qr-code ^5.0)
```

---

## ⚙️ Tecnologias Utilizadas

- **PHP (puro)**: lógica de backend e geração de imagem com GD
- **GD Library**: criação e edição de imagens dinamicamente (texto, QR Code, compositing)
- **endroid/qr-code**: biblioteca PHP para gerar QR Code em PNG a partir do vCard
- **HTML/CSS**: interface com modal e estilização moderna
- **JavaScript**: controle dos modais

---

## 🖼️ Como Funciona

1. O usuário acessa `/assinaturas/index.php`.
2. Seleciona o logo da empresa desejada (o botão abre um modal).
3. No modal, preenche **Primeiro Nome**, **Sobrenome**, **Cargo**, **E-mail** e **Telefone**.
4. Ao clicar em **Gerar Assinatura**, um `POST` é enviado para `gerar.php`.
5. O `gerar.php` executa:

   1. Monta uma string **vCard 3.0** usando as regras:

      ```
      N:Sobrenome;PrimeiroNome;;;
      FN:PrimeiroNome Sobrenome
      ORG:Nome da Empresa
      TITLE:Cargo
      TEL;TYPE=CELL:Telefone
      EMAIL:Email
      ```

   2. Gera um **QR Code em PNG** (300×300 px) do vCard usando a biblioteca **endroid/qr-code**.
   3. Cria um canvas branco do mesmo tamanho da imagem-base (fundo branco) e cola a base (PNG com possíveis transparências) sobre esse fundo.
   4. Redimensiona e sobrepõe o QR Code no canto superior-direito (configurado para 120×120 px, com margem de 10 px).
   5. Desenha os textos (**Primeiro Nome + Sobrenome**, **Cargo**, **Telefone**, **E-mail**) nas posições pré-definidas, respeitando cor e fonte de cada empresa.
   6. Envia a imagem resultante como PNG com fundo branco (`Content-Type: image/png`).

6. A assinatura é exibida/baixada como um arquivo PNG de fundo branco, pronta para colar em clientes de e-mail.
7. Ao apontar a câmera do smartphone para o QR Code, o dispositivo reconhece o vCard e oferece “Adicionar Contato” automaticamente.

---

## 🏢 Empresas Configuradas

Todas as empresas e suas configurações visuais ficam em `empresas.php`. Cada empresa possui:

```php
'nome'         => 'Nome exibido na assinatura',
'logo'         => 'Caminho para o botão com logo (assets/[empresa]/btn.png)',
'base'         => 'Imagem base para sobrepor dados + QR (assets/[empresa]/base.png)',
'fonte'        => 'Fonte TTF usada para texto (fonts/[...]/*.ttf)',
'cor'          => 'Cor principal para o nome (hex)',
'cor_telefone' => 'Cor utilizada para o telefone (hex)',
```

> **Importante:**
>
> 1. Remova qualquer QR Code estático das imagens base em `assets/[empresa]/base.png`.
> 2. As imagens base devem ter fundo transparente ou colorido, mas sem QR.
> 3. O script `gerar.php` montará o QR dinamicamente.

---

## 📌 Pré-requisitos

- **PHP 7.4** ou superior
- Extensão **GD** habilitada (para manipulação de imagens)
- **Composer** (para instalar a dependência `endroid/qr-code`)
- Servidor web que aceite execução de scripts PHP (Apache, Nginx, XAMPP, MAMP, etc.)

---

## 🚀 Como rodar localmente

1. **Clone** este repositório:

   ```bash
   git clone https://github.com/tiagofcamargo/gerar-assinaturas.git
   ```

2. Acesse a pasta do projeto e instale as dependências:

   ```bash
   cd gerar-assinaturas
   composer install --no-dev --optimize-autoloader
   ```

3. Configure um servidor local (XAMPP, MAMP, Valet, Laragon etc.) apontando para a pasta `/gerar-assinaturas`.
4. Abra no navegador:

   ```
   http://localhost/seu-host/gerar-assinaturas/index.php
   ```

5. Selecione a empresa, preencha os dados e clique em **Gerar Assinatura**.

   - O arquivo PNG será gerado automaticamente com fundo branco, QR Code vCard e informações inseridas.
   - Os dados não ficam armazenados em lugar nenhum — só são usados para criar a imagem e, em seguida, descartados.

---

## 🛡️ Segurança & Privacidade

- **Nenhum dado é persistido**: as informações do usuário são utilizadas apenas para gerar o PNG e não são gravadas em banco ou arquivo.
- **Responsabilidade do Usuário**: o uso da assinatura (incluindo distribuição do QR Code que contém dados de contato) é de total responsabilidade do colaborador que a gera.
- Para cenários externos ou públicos, recomenda-se adicionar validações extras nos inputs (e-mail, telefone, caracteres inválidos, etc.).

---

## 📄 Licença

Este projeto é livre para uso interno da CS Infra. Para uso externo ou comercial, consulte os responsáveis pela TI da organização.

---

## 🙌 Contribuições

Este projeto foi desenvolvido para padronizar assinaturas de e-mail no grupo CS Infra.
Sugestões de melhorias, correções de bugs ou adição de novas funcionalidades são bem-vindas!
Aberturas de _issues_ e _pull requests_ podem ser feitas diretamente neste repositório.
