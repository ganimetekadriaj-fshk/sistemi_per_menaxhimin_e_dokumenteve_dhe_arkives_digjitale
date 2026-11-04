Punimi i detyrës:
SISTEMI PËR MENAXHIMIN E DOKUMENTEVE DHE ARKIVËS DIGJITALE
Lënda: Projekt
Studenti/ja, ID: Ganimete Kadriaj 240329096		             Profesori: Endrit Fetahu 			
1. Hyrje
Projekti “Sistemi për Menaxhimin e Dokumenteve dhe Arkivës Digjitale” synon të krijojë një aplikacion web për ruajtjen,
organizimin, kërkimin dhe arkivimin elektronik të dokumenteve. Sistemi do ta lehtësojë gjetjen e dokumenteve,
do të kursejë kohë dhe do të mundësojë kontroll më të mirë të qasjes.
3. Përshkrimi i problemit
Ruajtja e dokumenteve në foldera të ndryshëm ose në formë fizike e vështirëson organizimin dhe gjetjen e tyre.
Projekti propozon një sistem të centralizuar ku dokumentet ruhen sipas kategorive dhe përdoren vetëm nga personat e autorizuar.
3. Qëllimi dhe objektivat
Qëllimi është zhvillimi i një sistemi të thjeshtë dhe të sigurt për menaxhimin e dokumenteve digjitale.
• Kyçja e përdoruesve dhe menaxhimi i roleve.
• Ngarkimi dhe ruajtja e dokumenteve.
• Organizimi sipas kategorive dhe kërkimi sipas emrit.
• Shikimi, shkarkimi, arkivimi dhe rikthimi i dokumenteve.
• Ruajtja e të dhënave në një databazë relacionale.
4. Përdoruesit e sistemit
Administratori
Administratori menaxhon llogaritë e përdoruesve, aktivizon ose çaktivizon llogaritë,
administron kategoritë dhe kontrollon qasjen në dokumente.
Përdoruesi i regjistruar
Përdoruesi mund të kyçet, të ngarkojë dokumente, të plotësojë përshkrimet, të kërkojë,
të shikojë dhe të shkarkojë dokumentet për të cilat ka leje.
6. Funksionalitetet kryesore
• Autentikimi: verifikimi i kredencialeve gjatë kyçjes.
• Ngarkimi: shtimi i dokumenteve dhe i të dhënave përshkruese.
• Kategorizimi: organizimi i dokumenteve sipas kategorive.
• Kërkimi dhe filtrimi: gjetja e dokumenteve sipas emrit ose kategorisë.
• Shikimi dhe shkarkimi: qasje vetëm për përdoruesit e autorizuar.
• Arkivimi dhe rikthimi: ruajtja e dokumenteve të arkivuara pa i fshirë fizikisht.
• Menaxhimi i përdoruesve: administrimi i llogarive dhe roleve.
6. Kërkesat e sistemit
Kërkesat funksionale
Sistemi duhet të mundësojë kyçjen, ngarkimin dhe ruajtjen e dokumenteve, kërkimin sipas emrit,
filtrimin sipas kategorisë, shkarkimin sipas autorizimit, arkivimin dhe rikthimin e dokumenteve,
si dhe menaxhimin e përdoruesve dhe kategorive. Para ngarkimit, duhet të kontrollohet lloji dhe madhësia e skedarit.
Kërkesat jofunksionale
Sistemi duhet të mbrojë fjalëkalimet, të kontrollojë identitetin dhe lejet e përdoruesve,
të ketë ndërfaqe të përshtatshme për kompjuter dhe telefon, të ofrojë kërkim të shpejtë dhe të përdorë strukturë
të organizuar të kodit dhe databazës.
8. Teknologjitë e zgjedhura
• Java – për logjikën dhe funksionet e aplikacionit.
• Spring Boot – për backend-in dhe API-të.
• HTML, CSS dhe JavaScript – për ndërtimin e ndërfaqes.
• MySQL – për ruajtjen e të dhënave të përdoruesve, dokumenteve dhe kategorive.
• Git dhe GitHub – për menaxhimin e versioneve dhe ruajtjen e kodit.
8. Arkitektura e sistemit
Sistemi do të ketë tri pjesë kryesore: frontend-in, backend-in dhe databazën.
Frontend-i paraqet ndërfaqen; backend-i, i zhvilluar me Java dhe Spring Boot,
përpunon kërkesat dhe kontrollon autorizimet; MySQL ruan të dhënat strukturore.
Skedarët do të ruhen në hapësirë të dedikuar, ndërsa databaza do të përmbajë të dhënat përshkruese dhe referencat e tyre.
Rrjedha e funksionimit: përdoruesi kyçet, dërgon një kërkesë, backend-i kontrollon të dhënat dhe lejet,
komunikon me databazën dhe ia kthen rezultatin ndërfaqes.
10. Modeli fillestar i databazës
Databaza do të përmbajë katër tabela kryesore:
• Users – të dhënat e përdoruesve, email-i, fjalëkalimi i hash-uar dhe roli.
• Categories – emri dhe përshkrimi i kategorive.
• Documents – emri, përshkrimi, vendndodhja e skedarit, madhësia, data dhe gjendja e arkivimit.
• Document_Access – lidh përdoruesit me dokumentet dhe përcakton lejet e qasjes.
Një përdorues mund të ngarkojë shumë dokumente dhe një kategori mund të përmbajë shumë dokumente.
Qasja e përdoruesve në dokumente realizohet përmes tabelës Document_Access.
10. Plani i realizimit
Në fazën e ardhshme do të krijohet projekti Java me Spring Boot, do të lidhet me MySQL dhe do të krijohen tabelat.
 Më pas do të ndërtohet ndërfaqja, do të implementohen kyçja, rolet, ngarkimi, listimi dhe kërkimi i dokumenteve,
dhe do të testohen funksionet kryesore.
12. Kufizimet e projektit
Faza fillestare përfshin menaxhimin bazë, kategorizimin, kontrollin e qasjes dhe arkivimin.
Nënshkrimi elektronik, integrimi me sisteme të jashtme, OCR dhe versionimi i avancuar nuk përfshihen në këtë fazë,
por mund të shtohen më vonë.
12. Përfundim
Projekti propozon një zgjidhje digjitale për menaxhimin më të lehtë dhe më të sigurt të dokumenteve.
Janë përcaktuar problemi, përdoruesit, funksionalitetet, kërkesat, teknologjitë, arkitektura dhe modeli fillestar i databazës.
 Këto përbëjnë bazën për implementimin dhe testimin e sistemit
RAPORTI II – Implementimi i Databazës dhe Backend-it
Në fazën e dytë të projektit “Sistemi për Menaxhimin e Dokumenteve dhe Arkivës Digjitale” është realizuar implementimi i databazës dhe zhvillimi i pjesës bazë të backend-it.
1. Databaza
Është krijuar databaza document_archive_db në MariaDB, e cila përmban katër tabela kryesore:
Users – ruajtja e të dhënave të përdoruesve.
Categories – organizimi i dokumenteve sipas kategorive.
Documents – ruajtja e informacioneve për dokumentet.
Document_Access – menaxhimi i lejeve të qasjes në dokumente.
Gjithashtu, janë përcaktuar relacionet ndërmjet tabelave përmes Primary Keys dhe Foreign Keys, si dhe janë përdorur kufizime të integritetit të të dhënave, si NOT NULL, UNIQUE, DEFAULT dhe ENUM.
2. Backend-i dhe REST API
Backend-i është zhvilluar duke përdorur Java dhe Spring Boot, ndërsa komunikimi me databazën realizohet përmes Spring Data JPA.
Është krijuar REST API për menaxhimin e dokumenteve, e cila mbështet operacionet kryesore CRUD:
GET – shfaqja e dokumenteve.
POST – regjistrimi i dokumenteve të reja.
PUT – përditësimi i dokumenteve ekzistuese.
DELETE – fshirja e dokumenteve.
3. Validimi dhe testimi
Janë implementuar rregullat e validimit të të dhënave përmes Jakarta Validation, duke përdorur anotime si @NotBlank, @Size, @NotNull dhe @PositiveOrZero.
Funksionalitetet e API-së janë testuar përmes Postman, ku janë verifikuar kërkesat GET, POST, PUT dhe DELETE, si dhe trajtimi i gabimeve përmes kodeve përkatëse HTTP.
4. Përfundimi
Në këtë fazë është realizuar baza funksionale e databazës dhe backend-it, duke mundësuar menaxhimin e të dhënave të dokumenteve përmes REST API-së. Në fazat e ardhshme planifikohet implementimi i ngarkimit real të skedarëve, autentikimit dhe autorizimit të përdoruesve, si dhe plotësimi i funksionaliteteve të arkivimit.
