pipeline {
  agent any
  environment {
    COMPOSER_NO_INTERACTION = '1'
    XDEBUG_MODE = 'coverage'
    SONAR_SCANNER_HOME = tool 'TESTSONAR'
  }

  stages {
    stage('Checkout') {
      steps { checkout scm }
    }

    stage('Instalar dependencias') {
      when { branch 'qa' }
      steps {
        bat '''
        php -v
        rem -- Si no hay composer, descargarlo local
        if exist composer.phar (
          echo Composer local ya existe
        ) else (
          curl -sS https://getcomposer.org/installer -o composer-setup.php
          php composer-setup.php --filename=composer.phar
        )
        php composer.phar install --no-progress --prefer-dist
        '''
      }
    }

    stage('Preparar entorno de pruebas') {
      when { branch 'qa' }
      steps {
        bat '''
        rem === 1) .env para testing ===
        if exist ".env.testing" (
          copy /Y .env.testing .env >NUL
        ) else (
          if not exist .env copy /Y .env.example .env >NUL
        )
    
        rem === 2) Forzar SQLite de pruebas ===
        powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' -replace '^DB_DATABASE=.*','DB_DATABASE=database/testing.sqlite' | Set-Content .env"
    
        if not exist database mkdir database
        if not exist database\\testing.sqlite type NUL > database\\testing.sqlite
    
        rem === 3) Generar clave y ESCRIBIRLA en .env (reemplazar o agregar) ===
        for /f %%K in ('php -r "echo base64_encode(random_bytes(32));"') do set "APPKEY=base64:%%K"
        powershell -Command ^
          "$envPath='.env';" ^
          "$c=Get-Content $envPath;" ^
          "if ($c -match '^APP_KEY=') { ($c -replace '^APP_KEY=.*', 'APP_KEY=%APPKEY%') | Set-Content $envPath } else { Add-Content $envPath \"`r`nAPP_KEY=%APPKEY%\" }"
    
        rem === 4) Limpiar caches DESPUES de fijar la clave ===
        if exist bootstrap\\cache\\config.php del /F /Q bootstrap\\cache\\config.php
        if exist bootstrap\\cache\\packages.php del /F /Q bootstrap\\cache\\packages.php
        php artisan config:clear
    
        rem === 5) Comprobación visual ===
        type .env | findstr /I ^APP_KEY
        '''
      }
    }


   stage('Tests + Coverage') {
      when { branch 'qa' }
      steps {
        bat '''
        if not exist storage\\coverage mkdir storage\\coverage
    
        rem === 1) Leer APP_KEY del .env y exportarla a este proceso ===
        for /f "tokens=2 delims==" %%A in ('findstr /R "^APP_KEY=" .env') do set "APP_KEY=%%A"
        set APP_ENV=testing
    
        rem === 2) Ejecutar PHPUnit (elige con o sin cobertura según tu PHP tenga Xdebug/PCOV) ===
        if exist vendor\\bin\\phpunit.bat (
          vendor\\bin\\phpunit.bat --coverage-clover storage\\coverage\\coverage.xml
        ) else (
          php vendor\\phpunit\\phpunit\\phpunit --coverage-clover storage\\coverage\\coverage.xml
        )
        '''
      }
    }
    stage('SonarQube') {
      when { branch 'qa' }
      steps {
        withSonarQubeEnv('TEST SONAR') {
           
          bat """
                "%SONAR_SCANNER_HOME%\\bin\\sonar-scanner.bat" ^
                  -D"sonar.projectKey=PQAFINAL" ^
                  -D"sonar.host.url=http://localhost:9000" ^
                  -D"sonar.projectName=PQAFINAL" ^
                  -D"sonar.sources=app,config,resources,routes" ^
                  -D"sonar.exclusions=vendor/**,storage/**,node_modules/**" ^
                  -D"sonar.php.coverage.reportPaths=storage/coverage/coverage.xml" ^
                  -D"sonar.token=sqa_71106708bbc573d335477bcc05e125532b36aa6c"
                """
            
        }
      }
    }
  }

     post {
  success {
    script {
      if (env.BRANCH_NAME == 'qa') {
        slackSend(
          channel: '#ci-qa',
          color: 'good',
          message: """
:white_check_mark: *QA PASÓ*
Job: ${env.JOB_NAME}  Build: #${env.BUILD_NUMBER}
Rama: ${env.BRANCH_NAME}
Ver: ${env.BUILD_URL}
""".stripIndent()
        )
      }
    }
  }
  failure {
    script {
      if (env.BRANCH_NAME == 'qa') {
        slackSend(
          channel: '#ci-qa',
          color: 'danger',
          message: """
:x: *QA FALLÓ*
Job: ${env.JOB_NAME}  Build: #${env.BUILD_NUMBER}
Rama: ${env.BRANCH_NAME}
Consola: ${env.BUILD_URL}console
""".stripIndent()
        )
      }
    }
  }
  unstable {
    script {
      if (env.BRANCH_NAME == 'qa') {
        slackSend(
          channel: '#ci-qa',
          color: '#e3b341',
          message: """
:warning: *QA INESTABLE*
Job: ${env.JOB_NAME}  Build: #${env.BUILD_NUMBER}
Rama: ${env.BRANCH_NAME}
Detalles: ${env.BUILD_URL}
""".stripIndent()
        )
      }
    }
  }
}

}
