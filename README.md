BarBarsBot
==========

Automated Script for online game BarBars.ru (php)

Данный скрипт нужен для автоматизации повторяющихся действий в игре.
Запускается на любом сервере с PHP не ниже версии 5. Работает без вмешательства пользователя.

Количество шагов работы задается в файле config.php

Запускается на выполнение main.php
Лог работы скрипта пишется в log.txt

Логин\пароль от аккаунта вводится в config.php

ОПИСАНИЕ ВОЗМОЖНОСТЕЙ СКРИПТА:

0. Поддержка двух сторон конфликта(севера и юга);

1. Автоматический логин;

2. Битва в "Башнях" за воинов и медиков. Поддерживается кастование, поиск и добивание врага(лечение и сжигание энергии для медиков) + добивание башни в локации;

3. При усталости или низком здоровье - герой перемещается в столицу для отдыха;

4. Починка вещей и разбор рюкзака(старые вещи разбираются на железо и мифрил);

5. Новые хорошие вещи, полученные в бою, герой сразу одевает на себя;

6. Автоматический ввод капчи при старте скрипта(если есть. Через сервис Antigate(можно подключить другие сервисы))(также,проверяется раз в основном цикле);

7. При запуске скрипта проверяется "Колодец Удачи" и забирается ежедневный подарок(также,проверяется раз в основном цикле);

8. Появилась возможность остановить работу скрипта, создав в корне скрипта файл "stop.txt";

9. Появилась поддержка игры в локации "Пещеры и драконы" с возможностью бить 5 из 6 локальных Боссов(также,повторяется раз в основном цикле);

10. В "башнях" реализована поддержка перехода по картам, если в текущей локации нет противника. Также поддерживается многоступенчатое возвращение в столицу при низких параметрах здоровья или энергии;

11. Проверка наличия "усталости". Если есть - не заходим в башни до ее окончания(возможно отключить в конфиге).
