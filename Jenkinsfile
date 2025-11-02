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
            rem -- Copiar .env de testing o example
            if exist ".env.testing" (
            copy /Y .env.testing .env >NUL
            ) else (
            if not exist .env copy /Y .env.example .env >NUL
            )

            rem -- Forzar sqlite (por si .env.testing no lo trae)
            powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' -replace '^DB_DATABASE=.*','DB_DATABASE=database/testing.sqlite' | Set-Content .env"

            rem -- sqlite file
            if not exist database mkdir database
            if not exist database\\testing.sqlite type NUL > database\\testing.sqlite

            rem -- Generar clave y luego limpiar cache de config
            php artisan key:generate --force
            php artisan config:clear

            rem -- (debug) mostrar si APP_KEY existe
            php -r "echo 'APP_KEY='.getenv('APP_KEY').PHP_EOL;"
            '''
        }
    }

    stage('Tests + Coverage') {
        when { branch 'qa' }
        steps {
            bat '''
            if not exist storage\\coverage mkdir storage\\coverage
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
        withSonarQubeEnv('sonarqube') {
          bat """
          "%SONAR_SCANNER_HOME%\\bin\\sonar-scanner.bat" ^
            -Dsonar.projectKey=PROYECTO-FINAL-QA ^
            -Dsonar.projectName=\\"PROYECTO FINAL QA\\" ^
            -Dsonar.sources=app,config,resources,routes ^
            -Dsonar.exclusions=vendor/**,storage/**,node_modules/** ^
            -Dsonar.php.coverage.reportPaths=storage/coverage/coverage.xml
          """
        }
      }
    }

    stage('Quality Gate') {
      when { branch 'qa' }
      steps {
        script {
          timeout(time: 10, unit: 'MINUTES') {
            def qg = waitForQualityGate()
            if (qg.status != 'OK') error "Quality Gate FAILED: ${qg.status}"
          }
        }
      }
    }
  }

  post {
    success { echo 'QA OK: listo para mergear a main.' }
    failure { echo 'Fallo QA: revisar tests o quality gate.' }
  }
}
