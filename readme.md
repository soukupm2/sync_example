Sync example

Pro testování systému Pohoda je třeba mít nainstalovaný 
[program Pohoda](https://www.stormware.cz/pohoda/start/).
Instalace programu je popsána [zde](https://www.stormware.cz/prirucka-pohoda-online/Uvod/Instalace/).

Pro testování systému FlexiBee není potřeba žádných programů. Pro testování je zde
[online demo](https://demo.flexibee.eu).

Pro spuštění projektu je možné použít docker.

Pokud nebude použit docker a chceme testovat pohodu, je nutné v souboru `config.neon`
změnit adresu u hodnoty `pohoda.baseUri` z `host.docker.internal` na `http://localhost`

Dále bude potřeba nainstalovat závislosti přes composer (pokud použijeme docker, je toto zařízeno při
spouštění kontejneru)
    
    composer install
