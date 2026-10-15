# Plugin Toggle Required Metadata

[English](README.md) · **Português (Brasil)** · [Español](README.es.md)

Este plugin permite que uma revista, editora ou servidor exija dos contribuidores de uma submissão mais do que a aplicação exige por conta própria: **Afiliação**, **Resumo da Biografia** e **ORCID iD** podem ser tornados obrigatórios.

Cada exigência é marcada no formulário de Contribuidor, onde o autor a preenche, e verificada de novo na conclusão da submissão, de modo que nenhuma submissão é recebida sem ela.

A exigência de ORCID depende do [plugin ORCID Profile](https://github.com/pkp/orcidProfile). Enquanto ele está habilitado, o iD não é digitado e sim autorizado pelo contribuidor, então o que este plugin exige é que **ao menos um contribuidor tenha um ORCID iD autenticado** e que a **autorização tenha sido solicitada a todos os demais**. Enquanto ele está desabilitado, o iD é digitado no formulário de Contribuidor, e todo contribuidor precisa ter um.

## Compatibilidade

Este plugin é compatível com as seguintes aplicações da PKP:

* OJS 3.4.0
* OPS 3.4.0
* OMP 3.4.0

Outras linhas da aplicação são atendidas por outras branches deste plugin: `stable-3_3_0` (OJS, OPS e OMP 3.3) e `main` (OJS, OPS e OMP 3.5).

## Instalação

Instale o plugin **Toggle Required Metadata** pela **Galeria de Plugins**, em **Painel > Website > Plugins**

## Configurações

Habilitar o plugin não exige nada por si só: cada exigência fica desligada até ser escolhida. Nas configurações do plugin, acessadas em **Painel > Website > Plugins**, você escolhe quais delas uma submissão precisa cumprir:

![A seção de configurações do plugin](/assets/plugin-settings-section.png)

![As configurações do plugin com as exigências habilitadas](/assets/plugin-settings.png)

## Onde cada exigência é aplicada

| Opção | Formulário de Contribuidor | Conclusão da submissão |
|---|---|---|
| Afiliação | marcada como obrigatória | bloqueia até que todo contribuidor tenha uma |
| Resumo da Biografia | marcado como obrigatório | bloqueia até que todo contribuidor tenha um |
| ORCID | marcado como obrigatório apenas enquanto o plugin ORCID Profile está desabilitado, já que um iD autorizado não pode ser digitado | bloqueia até que todo contribuidor tenha um iD ou, com o plugin ORCID Profile habilitado, até que um contribuidor tenha autenticado o seu e a autorização tenha sido solicitada aos demais |

### No formulário de Contribuidor

O metadado selecionado é marcado como obrigatório onde o autor o preenche, e o formulário não pode ser salvo sem ele:

![O formulário de Contribuidor com os metadados obrigatórios](/assets/application.png)

### Na conclusão da submissão

Toda exigência é verificada de novo na última etapa, e a submissão não pode ser enviada até que todas sejam cumpridas. Os avisos aparecem no topo do painel de contribuidores, ao lado do botão Editar que os resolve.

### O que a verificação não cobre

As exigências são sobre o que chega pelo formulário de submissão, e é aí que valem: são verificadas quando o autor conclui a submissão, e em nenhum outro lugar. Contribuidor editado depois no fluxo de trabalho, submissão criada pela API REST e tudo que entra por importação ou pelo QuickSubmit não passam pela verificação, nem a publicação passa. O que o plugin guarda é a porta: nenhuma submissão é *recebida* sem os metadados, o que não é o mesmo que nenhuma submissão da revista ficar sem eles.

## Com a submissão já no fluxo de trabalho

Depois que a submissão entra, a exigência de ORCID ainda pode estar esperando por um contribuidor que não autorizou o seu iD. O fluxo de trabalho diz quem, com a orientação que cabe a quem lê — um editor pode solicitar a autorização de novo, um autor só pode cobrar o contribuidor.

# Licença
__Este plugin está licenciado sob a GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
