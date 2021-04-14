# Komponenta odkaz na články

*Vypísanie odkazu na akýkoľvek článok na stránke. *

**Inštalácia**
1. nakopírovanie archývu do `app\components`,
2. do `app\AdminModule\presenters\ArticlePresenter` doplniť `use PeterVojtech\Clanky\OdkazNaClanky\odkazNaClankyTrait;`,
3. do `app\FrontModule\presenters\ClankyPresenter` doplniť `use PeterVojtech\Clanky\OdkazNaClanky\odkazNaClankyTrait;`,
4. do `app\config\komponenty.neon` doplniť:
```neon
parameters:
  komponenty:
#...
    odkazNaClanky:
      nazov: 'Odkaz na článok'
      jedinecna: FALSE  # Ci je mozne pridat len raz k clanku
      fa_ikonka: 'link'
      parametre: 
        id_hlavne_menu: 
          nazov: 'Id článku'
        template:
          nazov: 'Názov vzhľadu'
          hodnoty: 
            default: 'Základný'
            bez_avatara: 'Len odkaz bez avatara'
            to_foto_galery: 'Odkaz na fotogalériu'
            to_article: 'Odkaz na článok'
            foto_album: 'Odkaz pre fotogalériu'
#...

# Component Clanky\OdkazNaClanky
  - PeterVojtech\Clanky\OdkazNaClanky\IOdkazNaClankyControl
```