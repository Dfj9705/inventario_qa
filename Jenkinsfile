pipeline {
  agent any
  environment {
    COMPOSER_NO_INTERACTION = '1'
    XDEBUG_MODE = 'coverage'
    SONAR_SCANNER_HOME = tool 'SonarScanner'
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
        rem -- .env de testing (si no existe, copia el example)
        if exist ".env.testing" (
          copy /Y .env.testing .env >NUL
        ) else (
          if not exist .env copy /Y .env.example .env >NUL
        )

        rem -- clave app
        php artisan key:generate || exit /B 0

        rem -- sqlite de pruebas
        if not exist database mkdir database
        if not exist database\\testing.sqlite type NUL > database\\testing.sqlite

        php artisan config:clear || exit /B 0
        php artisan migrate --force || exit /B 0
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
