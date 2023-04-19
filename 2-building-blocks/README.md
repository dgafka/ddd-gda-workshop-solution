# Resilient Messaging Workshop

W tej części warsztatu zapoznamy się z tak zwanymi building blockami w [Ecotone Framework](https://docs.ecotone.tech/).  
Są to wzorce projektowe, które działają na stabilnych fundamentach architektury opartej o wiadomości.  
Co ważne będziemy skupiać się na pisaniu czystego PHP'owego kodu, a Ecotone połączy wszystkie elementy w całość.

# Wymagania

W celu uruchomienia warsztatu będzie nam potrzebny jedynie [Docker](https://docs.docker.com/engine/install/) i [Docker-Compose](https://docs.docker.com/compose/install/).

# Instalacja

1. Uruchom komendę `docker-compose pull && docker-compose up -d`
2. W momencie startu kontener z aplikacją zainstaluje dla nas wszystkie zależności. Można to sprawdzić przez `docker logs -f demo_development`
3. Jesteśmy gotowi do warsztatu.
4. Po zakończeniu ćwiczenia, aby usunąć wszystkie kontenery, wpisz komendę `docker-compose down`

# Zadanie do wykonania

W tym ćwieczeniu zbudujemy elektroniczny portfel, który będzie umożliwiał użytkownikowi zarządzanie swoimi pieniędzmi.
Jako, że potrzebujemy mieć pełną historię wpłat i wypłat skorzystamy z Event Sourcingu.  

Chcemy umożliwić użytkownikowi sprawdzenia swojego aktualnego stanu konta, stąd będziemy potrzebować projekcję, która zsumuje nam wszystkie wpłaty i wypłaty.

## Wywołanie zadania

Będziemy budować naszą aplikację w formacie TDD, oczekiwane zachowanie opisane jest już w testach, pozostało nam zaimplementowanie kodu, aby testy przechodziły :)  
Testy będziemy implementować od góry do dołu, gdy dany test będzie czerwony wywołanie się zatrzyma do momentu w którym zaimplementujemy kod, który sprawi, że test będzie zielony.  
W ten sposób będziemy przechodzić do kolejnych bardziej zaawansowanych testów.  
Testy znajdziesz w `tests/WalletTest.php` jednak nie będziemy tego kodu modyfikować :)
Aby wykonać testy wykonaj polecnie z konsoli: `docker exec -it ecotone_demo vendor/bin/phpunit --stop-on-failure`

Finalnie, gdy wszystkie testy przechodzą, możemy wywołać produkcyjneg wywołanie naszej aplikacji.
Wykonaj polecenie z konsoli: `docker exec -it ecotone_demo php run_example.php`

## Zadanie 1

Związku z tym, że `ShippingService` to zewnętrzny serwis, nie możemy polegać na jego dostępności.  
Dlatego chcąc rozdzielić zapis zamówienia od wywołania `ShippingService`, chcemy przetworzyć wysyłkę zamówenia korzystając z asynchronicznej wiadomości.  

1. Przerób `OrderService` aby zamiast wywoływać `ShippingService` opublikował `Event` `OrderWasPlaced`.  
2. Dodaj EventHandler który będzie nasłuchiwał na `OrderWasPlaced` i wywoływał `ShippingService` (Możesz go stworzyć w ramach klasy `src/Application/OrderService.php`).
3. Dodaj asynchroniczny kanał o nazwie `orders`, który będzie wysyłał wiadomości do RabbitMQ: `AmqpBackedMessageChannelBuilder::create("orders")` (Możesz go stworzyć w ramach klasy `src/Infrastructure/MessageChannelConfiguration.php`)
4. Wykorzystaj ten kanał, aby przeworzyć EventHandler `OrderWasPlaced` asynchronicznie.

### Podpowiedzi

- [Publikowanie eventów](https://docs.ecotone.tech/modelling/event-handling/dispatching-events#publishing)
- [Przetwarzanie eventów](https://docs.ecotone.tech/modelling/event-handling/handling-events#registering-class-based-event-handler)
- [Asynchroniczne przetwarzanie wiadomości](https://docs.ecotone.tech/modelling/asynchronous-handling#running-asynchronously)

## Zadanie 2 [Opcjonalne]

Message Broker (RabbitMQ), może nie być dostępny w momencie wysyłki wiadomości. 
W takim przypadku nie uda nam się zapisać zamówienia, lub go dostarczyć.  
Chcemy aby nasz system był odporny na takie przypadki.

1. Zaimplementuj mechanizm, który zamiast wysłania wiadomości do RabbitMQ, zapisze (wraz z Order'em) i przetworzy bezpośrednio z bazy danych.    
Wykorzystaj do tego kanał, który zapisuje wiadomości w bazie danych, zamiast `RabbitMQ`: `DbalBackedMessageChannelBuilder::create("orders")`. 

### Podpowiedzi

- [Outbox Pattern](https://docs.ecotone.tech/modelling/error-handling/outbox-pattern#dbal-message-channel)