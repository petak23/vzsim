# Komponenta pre zobrazenie favicon-ov

**Inštalácia**
1. nakopírovanie archývu do `app\components`,
2. do `app\AdminModule\presenters\BasePresenter` doplniť `use PeterVojtech\MainLayout\Favicon\faviconTrait;`,
3. do `app\FrontModule\presenters\BasePresenter` doplniť `use PeterVojtech\MainLayout\Favicon\faviconTrait;`,
4. do `app\config\services.neon` doplniť:
```neon
services:
  - PeterVojtech\MainLayout\Favicon\IFaviconControl

```
5. do hlavného template `@layout.latte` doplniť do hlavičky `{control favicon}`.