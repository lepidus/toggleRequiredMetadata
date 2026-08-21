# Plugin Toggle Required Metadata

[English](README.md) · **Português (Brasil)** · [Español](README.es.md)

Este plugin permite que uma revista, editora ou servidor exija dos contribuidores de uma submissão mais do que a aplicação exige por conta própria: a **Afiliação**, o **Resumo da Biografia** e o **ORCID iD** podem ser tornados obrigatórios, cada um separadamente.

Cada exigência é verificada na conclusão da submissão, de modo que nenhuma submissão passa sem ela. As três também são marcadas como obrigatórias no formulário de Contribuidor, onde o autor as preenche.

A exigência de ORCID é de outra natureza, porque desde a 3.5 ninguém digita um ORCID iD: ele só é registrado quando o contribuidor o autoriza pelo e-mail de verificação. Por isso o que o plugin exige é que **pelo menos um contribuidor tenha o ORCID iD verificado** e que a **verificação tenha sido solicitada a todos os demais**. Na última etapa da submissão isso aparece como um bloco único, que conta quantos contribuidores já verificaram e diz em que ponto cada um está, para que o autor distinga o que ainda depende dele do que apenas aguarda outra pessoa. Enquanto algum contribuidor seguir sem verificação, o fluxo de trabalho da submissão exibe um aviso nomeando-o.

## Compatibilidade

Este plugin é compatível com as seguintes aplicações da PKP:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Linhas de versão anteriores são atendidas por outros branches deste plugin: `stable-3_3_0` (OJS 3.3) e `stable-3_4_0` (OJS, OPS e OMP 3.4).

## Instalação

Instale o plugin **Toggle Required Metadata** pela **Galeria de Plugins**, em **Painel de Controle > Website > Plugins**

## Configurações

As três exigências vêm habilitadas por padrão. Para alterá-las:

1. Depois de instalado o plugin, vá até as configurações dele:
![plugin-settings-section](/assets/plugin-settings-section.png)

2. Selecione os metadados a serem exigidos. A opção ORCID só fica disponível enquanto o ORCID estiver habilitado nas configurações de ORCID da aplicação; quando não estiver, a opção aparece desabilitada, junto de uma nota explicando o motivo:
![plugin-settings](/assets/plugin-settings.png)

3. O formulário de Contribuidor passa então a marcar como obrigatórios os metadados selecionados:
![plugin-in-action](/assets/application.png)

## Onde cada exigência é aplicada

| Opção | Formulário de Contribuidor | Conclusão da submissão |
|---|---|---|
| Afiliação | marcada como obrigatória | bloqueada até que todo contribuidor tenha ao menos uma afiliação |
| Resumo da Biografia | marcado como obrigatório; o formulário não pode ser salvo sem ele | bloqueada até que todo contribuidor tenha um |
| ORCID | marcado como obrigatório ao lado do botão de verificação; não editável manualmente na 3.5 | bloqueada até que um contribuidor tenha o iD verificado e a verificação tenha sido solicitada a todos os demais, exibido como um bloco único com a situação de cada contribuidor |

## Notas sobre a 3.5

* **Não é possível exigir que o contribuidor digite um ORCID iD.** Desde a 3.5 a aplicação recusa qualquer ORCID iD enviado pelo formulário de Contribuidor; um iD só é registrado pelo fluxo de verificação do ORCID. Versões anteriores deste plugin exigiam o iD digitado sempre que o plugin ORCID Profile estivesse desligado, e isso não é mais possível.
* **A opção ORCID acompanha as configurações de ORCID da aplicação.** Com o ORCID desabilitado a opção não surte efeito, e a escolha salva é preservada e volta a valer assim que o ORCID for habilitado. Note que o ORCID também pode ser habilitado para toda a instalação, no nível do site, o que liga a exigência para todas as revistas, editoras ou servidores de uma vez.
* **A afiliação e o ORCID iD são marcados no formulário de Contribuidor, mas verificados na conclusão da submissão.** Os dois campos renderizam o próprio rótulo, então o plugin acrescenta um elemento ao lado de cada um para carregar a marca de obrigatoriedade. Nenhum dos dois é sinalizado como obrigatório para o próprio formulário: nenhum deles limpa um erro depois de levantado, o que deixaria o formulário de Contribuidor impossível de salvar.

## Compilação dos assets de front-end

As marcas de obrigatoriedade nos campos de afiliação e ORCID, o bloco de ORCID na última etapa da submissão e o aviso de ORCID no fluxo de trabalho são componentes Vue, compilados com o Vite. O resultado da compilação é versionado em `public/build/`, de modo que instalar o plugin não exige nenhum passo de build. Para recompilar após alterar qualquer coisa em `resources/`:

```
npm install
npm run build
```

# Licença
__Este plugin é licenciado sob a GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
