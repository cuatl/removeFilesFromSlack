<?php
   /* Remove old files from slack team. * @toro 2016-09-10 */

   setlocale(LC_ALL,'es_MX.UTF-8'); //locale settings
   $tiempo = strtotime("-2 MONTH"); //Filter files created before this timestamp (inclusive).
   /* get token at https://api.slack.com/web (test token it is ok!) */

   $token = "YOUR_TOKEN_HERE"; //token
   $datas = ["token"=> $token, "ts_to"=> $tiempo]; //data sent

   if (empty($token) || $token == 'YOUR_TOKEN_HERE') 
      die ($argv[0]." - invalid token\n");

   /* open connection */
   $ch = curl_init();

   curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
   curl_setopt($ch, CURLOPT_POST, 1); 
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_URL, "https://slack.com/api/files.list?token=".$token."&ts_to=".$tiempo);

   $result = curl_exec($ch);
   $data = json_decode($result);

   /* error */
   if (!isset($data->ok) or empty($data->files)) {
      echo "No existen archivos < ".strftime("%c", $tiempo).".\n";
       exit();
   }

   /* success! */
   echo "=== Encontramos ".sizeof($data->files)." archivos más viejos de ".strftime("%c", $tiempo)."\n\n";

   $i=0;

   foreach ($data->files AS $file) {
      $i++;
      if ($file->created > $tiempo) {
         echo "archivo más nuevo, no se elimina!\n";
         continue;
      }

      echo "- Eliminando ".$i."/".sizeof($data->files)." [".date('d/m/Y', $file->created)."] ".$file->name."... ";
      curl_setopt($ch, CURLOPT_POSTFIELDS, ['file'=>$file->id]);
      curl_setopt($ch, CURLOPT_URL,"https://slack.com/api/files.delete?token=".$token);

      $result = curl_exec($ch); 
      $tmp = json_decode($result);

      if (isset($tmp->ok))
         echo "ok!\n";
      else 
         echo $tmp->error."\n";
      sleep(1);
   }
   curl_close($ch);
   //EOF
