# Komponenta pre zobrazenie google analytics

**Inštalácia**
1. nakopírovanie archývu do `app\components`,
2. do `app\FrontModule\presenters\BasePresenter` doplniť `use PeterVojtech\MainLayout\GoogleAnalytics\googleAnalyticsTrait;`,
4. do `app\FrontModule\config\services.neon` doplniť:
```neon
services:
  - PeterVojtech\MainLayout\GoogleAnalytics\IGoogleAnalyticsControl

```
5. do hlavného template `@layout.latte` doplniť na koniec `{control googleAnalytics}`.