<html>
  <head>
    <script src="https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>
    <textarea id="debug" rows="60" cols="50" style="float:right"></textarea>
    <?php
      function debug_log($message) 
      {
        // Получаем ссылку на текстовое поле с помощью JavaScript
        echo "<script>var debug = document.getElementById('debug');</script>";
        // Добавляем сообщение в текстовое поле
        echo "<script>debug.value += '" . addslashes($message) . "' + '\\n';</script>";
      }

      header('Content-Type: text/html; charset=utf-8');
      mb_internal_encoding("UTF-8");
      mb_http_output("UTF-8");
      debug_log("стадия: загрузка переменных...");
      $type = "C";
      $exchange = "BINANCEF1";
      $pair = strtolower($_POST['currency']);
      $timeframe = strtolower($_POST['interval']);
      $strategy = $_POST['strategy'];
      $parameters = $_POST['parameters'];
      // Параметры соединения с базой данных
      $servername = "localhost";
      $username = "";
      $password = "";
      $dbname = "DevastorPrices_BINANCE";
      // Создание соединения
      debug_log("стадия: создание соединения с базой данных...");
      $conn = new mysqli($servername, $username, $password, $dbname);
      // Проверка соединения
      debug_log("стадия: проверка соединения с базой данных...");
      if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
      $str_query = $pair . "_" . $timeframe;
      // Запрос к таблице с данными
      debug_log("стадия: запрос к таблице данных свечей...");
      $sql = "SELECT time, open, high, low, close FROM devastorprices_binance_$str_query";
      $result = $conn->query($sql);
      $data = array();
      while ($row = $result->fetch_assoc()) 
      {
        // Преобразование данных в нужный формат
        $time = $row["time"] / 1000;
        $open = number_format($row["open"], 8, ".", "");
        $high = number_format($row["high"], 8, ".", "");
        $low = number_format($row["low"], 8, ".", "");
        $close = number_format($row["close"], 8, ".", "");
        $data[] = array
        (
          "time" => $time,
          "open" => $open,
          "high" => $high,
          "low" => $low,
          "close" => $close
        );
      }
      debug_log("стадия: преобразование данных в формат JSON...");
      // Преобразование данных в формат JSON
      $json_data = json_encode($data);
      //$command = "/usr/bin/mono /var/www/html/Debug/DevastorCountModelWeb.exe C";// $pair $timeframe $strategy $parameters '$json_data'";
      debug_log("стадия: вызов исполняегомо файла...");


      $command_to = "/usr/bin/mono /var/www/html/Debug/DevastorCountModelWeb.exe " . $type . " " . $exchange . " " . $pair . " " . $timeframe . " " . $strategy . " " . $_POST['start'] . " " . $_POST['end'] . " " . $parameters;
      //var_dump($command_to);

      // Проверяем, существует ли файл
      if (!file_exists("/var/www/html/Debug/DevastorCountModelWeb.exe")) 
      {
          echo "Ошибка: файл не найден.1";
      }
     
      $output = shell_exec($command_to);
      /*
      $process = proc_open($command_to, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);

      if(is_resource($process))
      {
          $status = proc_get_status($process);

          // Ждем завершения процесса
          while($status['running'])
          {
              $status = proc_get_status($process);
          }

          // Проверяем статус завершения процесса
          if($status['exitcode'] == 0)
          {
              echo "Программа успешно завершена.";
          }
          else
          {
              echo "Произошла ошибка при завершении программы.";
          }

          proc_close($process);
      }
      else
      {
          echo "Ошибка: файл не был запущен.";
      }
/*
      while (!file_exists("model_count.txt")) 
      {
        sleep(1); // ждем 1 секунду и проверяем снова
      }
      $dictionary = array();
      $file = fopen("model_count.txt", "r");
      while (!feof($file)) 
      {
          $line = trim(fgets($file));
          if (!empty($line)) 
          {
              list($key, $value) = explode(",", $line);
              $dictionary[$key] = $value;
          }
      }
      fclose($file);
      // вывод словаря
      debug_log("стадия: вывод словаря...");
      foreach ($dictionary as $key => $value) 
      {
        //debug_log($key . " - " . utf8_encode($value));
      }

*/


      
      $output = mb_convert_encoding($output, "UTF-8", "Windows-1251");
      // парсинг результата и создание словаря
      $dictionary = array();
      $lines = explode("\n", $output);
      foreach ($lines as $line) 
      {
        $line = trim($line);
        if (!empty($line)) 
        {
          $parts = explode(" - ", $line);
          $key = $parts[0];
          $value = $parts[1];
          $dictionary[$key] = $value;
        }
      }

      // вывод словаря
      debug_log("стадия: вывод словаря...");
      foreach ($dictionary as $key => $value) 
      {
        debug_log($key . " - " . utf8_encode($value));
      }
      //echo json_encode($dictionary);


      /*
      // преобразуем JSON-строку в массив
      debug_log("стадия: преобразование полученных данных в формат JSON...");
      $data_from = json_decode($dictionary, true);
      // создаем словарь
      debug_log("стадия: создание и заполнение словаря...");
      $data_LS = [];
      foreach ($data_from as $key => $value) 
      {
          $data_LS[$key] = (string)$value;
          debug_log(" >>>>  " . $key . ": " . (string)$value);
      }*/
    ?>
    <script>
      function convertDateToTimestamp(dateString) 
      {
        // Разбиваем строку на части и преобразуем каждую в число
        var parts = dateString.split(" ");
        var year = parseInt(parts[0]);
        var month = parseInt(parts[1]) - 1; // месяцы в объекте Date начинаются с 0
        var day = parseInt(parts[2]);
        var hours = parseInt(parts[3]);
        var minutes = parseInt(parts[4]);
        var seconds = parseInt(parts[5]);

        // Создаем объект Date с указанными значениями
        var date = new Date(year, month, day, hours, minutes, seconds);

        // Возвращаем метку времени в миллисекундах
        return (date.getTime() / 1000);
      }

      pair = <?php echo json_encode($pair); ?>;
      // ТЁМНАЯ ТЕМА::
      var chart = LightweightCharts.createChart(document.body, 
      {
        text: pair,
        width: 1280,
        height: 720,
        // ТЁМНАЯ ТЕМА::
        timeScale: 
        {
          timeVisible: true,
          borderColor: '#333333', // изменено
          reverse: true,
        },
        rightPriceScale: 
        {
          borderColor: '#333333', // изменено
        },
        layout: 
        {
          background: 
          {
            type: 'solid',
            color: '#131722', // изменено
          },
          textColor: '#d1d4dc', // изменено
        },
        grid: 
        {
          horzLines: 
          {
            color: '#1f2943', // изменено
          },
          vertLines: 
          {
            color: '#1f2943', // изменено
          },
        },
        /*
        // СВЕТЛАЯ ТЕМА::
        timeScale: 
        {
          timeVisible: true,
          borderColor: '#D1D4DC',
          reverse: true, // добавлено изменение
        },
        rightPriceScale: 
        {
          borderColor: '#D1D4DC',
        },
        layout: 
        {
          background: 
          {
            type: 'solid',
            color: '#ffffff',
          },
          textColor: '#000',
        },
        grid: 
        {
          horzLines: 
          {
            color: '#F0F3FA',
          },
          vertLines: 
          {
            color: '#F0F3FA',
          },
        },
        */
      });
      var series = chart.addCandlestickSeries
      (
      {
        upColor: 'rgb(38,166,154)',
        downColor: 'rgb(255,82,82)',
        wickUpColor: 'rgb(38,166,154)',
        wickDownColor: 'rgb(255,82,82)',
        borderVisible: false,
      }
      );
      chart.applyOptions
      (
      {
        rightPriceScale: 
        {
          scaleMargins: 
          {
            top: 0.2,
            bottom: 0.2,
          },
          borderVisible: false,
          mode: 0,
          autoScale: true,
          invertScale: false,
          alignLabels: true,
          drawTicks: true,
          scaleSpacing: 40,
          setCustomPriceFormatter: (price) => 
          {
            if (price < 1) 
            {
              return price.toFixed(8);
            }
            return price.toFixed(2);
          },
        },
      }
      );
      data = <?php echo $json_data ?>;
      //data_LS = <?php echo $dictionary ?>;
      data_LS = <?php echo json_encode($dictionary); ?>;

      //data_LS = <?php echo json_encode($dictionary); ?>;
      //console.log(data);
      series.setData(data);
      var datesForMarkers = [data[data.length - 7], data[data.length - 1]];
      var indexOfMinPrice = 0;
      for (var i = 1; i < datesForMarkers.length; i++) 
      {
        if (datesForMarkers[i].high < datesForMarkers[indexOfMinPrice].high) 
        {
          indexOfMinPrice = i;
        }
      }

      var markers = [];
      for (var key in data_LS) 
      {
        console.log(key + ": " + data_LS[key] + " ... " + convertDateToTimestamp(key));
        if (data_LS[key] == "LONG") 
        {
          markers.push(
          {
            time: convertDateToTimestamp(key),
            position: 'belowBar',
            color: '#00ff00',
            shape: 'arrowUp',
            text: 'BUY'
          });
        } 
        else if (data_LS[key] == "SHORT") 
        {
          markers.push(
          {
            time: convertDateToTimestamp(key),
            position: 'aboveBar',
            color: '#ff0000',
            shape: 'arrowDown',
            text: 'SELL'
          });
        }
      }
      series.setMarkers(markers);
    </script>
  </body>
</html>
<?php
  // Закрытие соединения с базой данных
  $conn->close();
?>






