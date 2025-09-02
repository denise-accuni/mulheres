#Mulheres em Cargos em Empresas de Tecnologia

import pandas as pd

rows = [
    # EUA
    ["Safra Catz", "EUA", "CEO, Oracle (2014– )", "Consolidou a Oracle como força em banco de dados e nuvem, liderando aquisições e eficiência operacional.", "https://en.wikipedia.org/wiki/Safra_Catz"],
    ["Lisa Su", "EUA", "CEO, AMD (2014– )", "Virada histórica da AMD e liderança em chips de alto desempenho para PCs, data centers e IA.", "https://en.wikipedia.org/wiki/Lisa_Su"],
    ["Ginni Rometty", "EUA", "CEO, IBM (2012–2020)", "Conduziu a IBM para serviços de nuvem e IA e lançou iniciativas de requalificação de profissionais.", "https://en.wikipedia.org/wiki/Ginni_Rometty"],
    ["Susan Wojcicki", "EUA", "CEO, YouTube (2014–2023)", "Expandiu o YouTube e a economia de criadores, integrando plataformas de monetização e conteúdo.", "https://en.wikipedia.org/wiki/Susan_Wojcicki"],
    ["Sheryl Sandberg", "EUA", "COO, Facebook/Meta (2008–2022)", "Construiu o negócio global de anúncios digitais do Facebook e impulsionou a escalabilidade da plataforma.", "https://en.wikipedia.org/wiki/Sheryl_Sandberg"],
    ["Ruth Porat", "EUA", "President & CIO e ex‑CFO, Alphabet (2015– )", "Orquestrou disciplina de capital e investimentos em IA e infraestruturas do Google/Alphabet.", "https://en.wikipedia.org/wiki/Ruth_Porat"],
    ["Meg Whitman", "EUA", "CEO, HP/HPE (2011–2018)", "Liderou a separação da HP em HP Inc. e HPE e focou o portfólio em impressão, PCs e soluções corporativas.", "https://en.wikipedia.org/wiki/Meg_Whitman"],
    ["Marissa Mayer", "EUA", "CEO, Yahoo (2012–2017)", "Acelerou o foco móvel e realizou aquisições estratégicas; sua gestão marcou a reta final do Yahoo como independente.", "https://en.wikipedia.org/wiki/Marissa_Mayer"],
    ["Gwynne Shotwell", "EUA", "Presidente & COO, SpaceX (2008– )", "Comandou a escalabilidade de lançamentos reutilizáveis e a implantação global do Starlink.", "https://en.wikipedia.org/wiki/Gwynne_Shotwell"],
    ["Colette Kress", "EUA", "CFO, NVIDIA (2013– )", "Arquitetou a estratégia financeira durante o boom de IA, apoiando o crescimento explosivo em data centers.", "https://en.wikipedia.org/wiki/Colette_Kress"],
    ["Amy Hood", "EUA", "CFO, Microsoft (2013– )", "Pilar da transformação em nuvem e M&A (como Activision), preservando disciplina financeira.", "https://en.wikipedia.org/wiki/Amy_Hood"],
    ["Julie Sweet", "EUA", "Chair & CEO, Accenture (2019– )", "Impulsionou serviços digitais, nuvem e IA em escala global, com foco em responsabilidade e inclusão.", "https://en.wikipedia.org/wiki/Julie_Sweet"],
    ["Whitney Wolfe Herd", "EUA", "Fundadora & CEO, Bumble (2014– )", "Popularizou o modelo de app de relacionamento ‘ela dá o primeiro passo’ e se tornou a mulher mais jovem a abrir capital (2021).", "https://en.wikipedia.org/wiki/Whitney_Wolfe_Herd"],
    ["Anne Wojcicki", "EUA", "Cofundadora e líder, 23andMe", "Popularizou testes genéticos diretos ao consumidor e parcerias para P&D farmacêutica.", "https://en.wikipedia.org/wiki/Anne_Wojcicki"],
    ["Cathie Lesjak", "EUA", "CFO e CEO interina, HP (2010)", "Garantiu continuidade e transparência na transição de liderança da HP.", "https://en.wikipedia.org/wiki/Cathie_Lesjak"],
    ["Diane M. Bryant", "EUA", "COO, Google Cloud (2017–2018); ex‑líder do Data Center Group da Intel", "Ajudou a posicionar Google Cloud para o mercado corporativo e escalou a divisão de data centers da Intel.", "https://en.wikipedia.org/wiki/Diane_M._Bryant"],
    ["Renée James", "EUA", "Fundadora & CEO, Ampere (2017– ); ex‑presidente, Intel", "Pioneira em servidores ARM para nuvem, ampliando competição em data centers.", "https://en.wikipedia.org/wiki/Ren%C3%A9e_James"],
    ["Peggy Johnson", "EUA", "ex‑CEO, Magic Leap (2020–2023); hoje CEO, Agility Robotics", "Redirecionou a AR para o mercado corporativo e agora lidera robótica humanoide para aplicações industriais.", "https://en.wikipedia.org/wiki/Peggy_Johnson"],
    ["Katrina Lake", "EUA", "Fundadora e ex‑CEO, Stitch Fix", "Aplicou ciência de dados ao varejo de moda, inaugurando o ‘personal styling’ em escala.", "https://en.wikipedia.org/wiki/Katrina_Lake"],
    ["Padmasree Warrior", "EUA", "ex‑CTO, Cisco e Motorola; fundadora & CEO, Fable", "Influenciou a infraestrutura de redes móveis e hoje promove leitura social com tecnologia.", "https://pt.wikipedia.org/wiki/Padmasree_Warrior"],
    ["Diane Greene", "EUA", "Cofundadora & ex‑CEO, VMware; ex‑líder do Google Cloud", "Pioneira em virtualização, base da computação em nuvem moderna.", "https://en.wikipedia.org/wiki/Diane_Greene"],
    ["Linda Yaccarino", "EUA", "CEO, X/Twitter (2023–2025)", "Guiou a plataforma em fase de rebranding e recuperação de anunciantes.", "https://en.wikipedia.org/wiki/Linda_Yaccarino"],
    ["Adena Friedman", "EUA", "Chair & CEO, Nasdaq (2017– )", "Primeira mulher a liderar uma bolsa global; impulsionou soluções de mercado baseadas em nuvem e dados.", "https://en.wikipedia.org/wiki/Adena_Friedman"],
    ["Ursula Burns", "EUA", "CEO, Xerox (2009–2016)", "Primeira mulher negra a comandar uma Fortune 500; liderou a transição para serviços.", "https://en.wikipedia.org/wiki/Ursula_Burns"],
    ["Anne M. Mulcahy", "EUA", "CEO, Xerox (2001–2009)", "Orquestrou o ‘turnaround’ da Xerox após crise de governança e dívida.", "https://en.wikipedia.org/wiki/Anne_M._Mulcahy"],
    ["Carol Bartz", "EUA", "CEO, Yahoo (2009–2011)", "Conduziu reestruturação do Yahoo após turbulências estratégicas.", "https://en.wikipedia.org/wiki/Carol_Bartz"],
    ["Susan Li", "EUA", "CFO, Meta (2022– )", "Conduz a disciplina financeira na expansão em IA, Reels e metaverso.", "https://en.wikipedia.org/wiki/Susan_Li_(businesswoman)"],
    ["Anat Ashkenazi", "EUA", "CFO, Alphabet/Google (2024– )", "Transição financeira para a era de IA generativa no Google.", "https://en.wikipedia.org/wiki/Anat_Ashkenazi"],
    ["Tracy Chou", "EUA", "Fundadora & CEO, Block Party; cofundadora, Project Include", "Promoveu transparência em diversidade no setor e criou ferramentas contra assédio on‑line.", "https://en.wikipedia.org/wiki/Tracy_Chou_(software_engineer)"],
    ["Corie Barry", "EUA", "CEO, Best Buy (2019– )", "Lidera a maior varejista de eletrônicos dos EUA com foco em serviços e experiência do cliente.", "https://corporate.bestbuy.com/our-leadership/corie-barry/"],

    # Canadá
    ["Michelle Zatlyn", "Canadá", "Cofundadora, Presidente & COO, Cloudflare", "Escalou rede global de segurança e performance da Web, levando CDN/Zero Trust a milhões de domínios.", "https://en.wikipedia.org/wiki/Michelle_Zatlyn"],
    ["Shahrzad Rafati", "Canadá", "Fundadora & CEO, BroadbandTV (BBTV)", "Pioneira em monetização de criadores e gestão de conteúdo em plataformas digitais.", "https://en.wikipedia.org/wiki/Shahrzad_Rafati"],

    # Europa
    ["Christel Heydemann", "França", "CEO, Orange (2022– )", "Comanda transformação 5G/fibra e serviços B2B de cibersegurança e nuvem do grupo.", "https://en.wikipedia.org/wiki/Christel_Heydemann"],
    ["Margherita Della Valle", "Itália/Reino Unido", "CEO, Vodafone Group (2023– )", "Reestrutura portfólio europeu e acelera estratégia em redes e serviços digitais.", "https://en.wikipedia.org/wiki/Margherita_Della_Valle"],
    ["Gillian Tans", "Holanda", "CEO, Booking.com (2016–2019)", "Expandiu o marketplace global de viagens e fortaleceu operações internacionais.", "https://en.wikipedia.org/wiki/Gillian_Tans"],
    ["Anne Boden", "Reino Unido", "Fundadora & ex‑CEO, Starling Bank", "Inovou em banco digital com infraestrutura própria, foco em PME e pagamentos.", "https://en.wikipedia.org/wiki/Anne_Boden"],
    ["Ana Maiques", "Espanha", "Cofundadora & CEO, Neuroelectrics", "Desenvolve dispositivos não‑invasivos para estimulação/monitoramento cerebral, com aplicações em depressão e epilepsia.", "https://en.wikipedia.org/wiki/Ana_Maiques"],
    ["Daniela Braga", "Portugal/EUA", "Fundadora & CEO, Defined.ai", "Construiu plataforma de dados para treinar IA, viabilizando soluções multilingues em larga escala.", "https://pt.wikipedia.org/wiki/Daniela_Braga_(empreendedora)"],

    # Ásia
    ["Lucy Peng (Peng Lei)", "China", "Cofundadora, Alibaba; liderança em Ant Financial", "Ajudou a estruturar ecossistema de e‑commerce e pagamentos digitais na China.", "https://en.wikipedia.org/wiki/Lucy_Peng"],
    ["Jean Liu", "China", "Presidente, Didi Chuxing", "Cresceu a plataforma de mobilidade para centenas de milhões de usuários.", "https://en.wikipedia.org/wiki/Jean_Liu"],
    ["Zhou Qunfei", "China", "Fundadora & CEO, Lens Technology", "Industrializou vidro de telas para smartphones, tornando‑se líder global na cadeia móvel.", "https://en.wikipedia.org/wiki/Zhou_Qunfei"],
    ["Cher Wang", "Taiwan", "Cofundadora & Chair, HTC/VIA", "Pioneira de smartphones e realidade virtual com o HTC Vive.", "https://en.wikipedia.org/wiki/Cher_Wang"],
    ["Roshni Nadar Malhotra", "Índia", "Chair, HCLTech (2020– )", "Primeira mulher a comandar uma gigante de TI listada na Índia; direciona estratégia global do grupo.", "https://en.wikipedia.org/wiki/Roshni_Nadar"],
    ["Falguni Nayar", "Índia", "Fundadora & CEO, Nykaa", "Impulsionou e‑commerce de beleza/fashion na Índia e realizou IPO de destaque.", "https://en.wikipedia.org/wiki/Falguni_Nayar"],
    ["Kiran Mazumdar‑Shaw", "Índia", "Fundadora & Chair, Biocon", "Consolidou biotecnologia indiana com medicamentos biológicos acessíveis.", "https://en.wikipedia.org/wiki/Kiran_Mazumdar%E2%80%93Shaw"],
    ["Tan Hooi Ling", "Malásia/Singapura", "Cofundadora, Grab", "Expandiu super‑app de mobilidade e fintech pelo Sudeste Asiático.", "https://en.wikipedia.org/wiki/Tan_Hooi_Ling"],
    ["Divya Gokulnath", "Índia", "Cofundadora & Diretora, BYJU’S", "Popularizou edtech de larga escala, levando aulas digitais e preparação a milhões.", "https://en.wikipedia.org/wiki/Divya_Gokulnath"],

    # África
    ["Funke Opeke", "Nigéria", "Fundadora & CEO, MainOne (vendida à Equinix)", "Conectividade e data centers que reduziram custos de internet na África Ocidental.", "https://en.wikipedia.org/wiki/Funke_Opeke"],
    ["Rebecca Enonchong", "Camarões/EUA", "Fundadora & CEO, AppsTech", "Evangelista de ecossistemas africanos de TI e soluções empresariais.", "https://en.wikipedia.org/wiki/Rebecca_Enonchong"],
    ["Juliet Anammah", "Nigéria", "Chair & Chief Sustainability Officer, Jumia", "Acelerou e‑commerce e logística digital no continente.", "https://en.wikipedia.org/wiki/Juliet_Anammah"],
    ["Njeri Rionge", "Quênia", "Cofundadora, Wananchi Group", "Pioneira em TV a cabo e internet de banda larga na África Oriental.", "https://en.wikipedia.org/wiki/Njeri_Rionge"],
    ["Rapelang Rabana", "África do Sul", "Fundadora, Rekindle Learning; cofundadora, Yeigo", "Educação corporativa digital e inovação em VoIP móvel na África.", "https://en.wikipedia.org/wiki/Rapelang_Rabana"],

    # América Latina
    ["Luiza Helena Trajano", "Brasil", "Chair, Magazine Luiza (Magalu)", "Transformação digital do varejo e forte impacto em inclusão e empreendedorismo.", "https://pt.wikipedia.org/wiki/Luiza_Trajano"],
    ["Cristina Junqueira", "Brasil", "Cofundadora e executiva, Nubank", "Escalou banco digital que ampliou a inclusão financeira no Brasil e na América Latina.", "https://en.wikipedia.org/wiki/Cristina_Junqueira"],
    ["Tânia Cosentino", "Brasil", "Presidente, Microsoft Brasil (2019– )", "Lidera estratégia de nuvem e IA no país, com agenda de sustentabilidade e diversidade.", "https://pt.wikipedia.org/wiki/Tania_Cosentino"],
]

df = pd.DataFrame(rows, columns=["Nome", "País", "Função", "Impacto (resumo)", "Fonte (link verificado)"])

# Save to files
csv_path = "/mnt/data/mulheres_lideres_empresas_tecnologia_2005-2025.csv"
xlsx_path = "/mnt/data/mulheres_lideres_empresas_tecnologia_2005-2025.xlsx"
df.to_csv(csv_path, index=False)
df.to_excel(xlsx_path, index=False)

import caas_jupyter_tools
caas_jupyter_tools.display_dataframe_to_user("Mulheres líderes em empresas de tecnologia (2005–2025)", df)

csv_path, xlsx_path
