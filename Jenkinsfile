pipeline {
  agent any
  environment {
    COMPOSER_NO_INTERACTION = '1'
    XDEBUG_MODE = 'coverage'                 // si tienes Xdebug/PCOV para cobertura
    SONAR_SCANNER_HOME = tool 'TESTSONAR' // nombre en Global Tool
  }

  stages {
    stage('Checkout') {
      steps { checkout scm }
    }

    stage('Instalar dependencias') {
      when { branch 'qa' }
      steps {
        // Instala composer local si no existe y luego dependencias
        sh '''
          php -v
          if ! command -v composer >/dev/null 2>&1; then
            php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
            php composer-setup.php --install-dir=./ --filename=composer
            rm -f composer-setup.php
            export COMPOSER=./composer
          else
            export COMPOSER=$(command -v composer)
          fi
          $COMPOSER install --no-progress --prefer-dist
        '''
      }
    }

    stage('Preparar entorno de pruebas') {
      when { branch 'qa' }
      steps {
        sh '''
          cp -f .env.testing .env || true
          php -r "file_exists('.env') || copy('.env.example','.env');"
          php artisan key:generate || true
          mkdir -p database && touch database/testing.sqlite || true
          php artisan config:clear || true
          php artisan migrate --force || true
        '''
      }
    }

    stage('Tests + Coverage') {
      when { branch 'qa' }
      steps {
        sh '''
          mkdir -p storage/coverage
          ./vendor/bin/phpunit --coverage-clover storage/coverage/coverage.xml
        '''
      }
      post {
        always {
          junit allowEmptyResults: true, testResults: 'tests/**/junit*.xml'
        }
      }
    }

    stage('SonarQube') {
      when { branch 'qa' }
      steps {
        withSonarQubeEnv('sonarqube') {
          sh """
            ${env.SONAR_SCANNER_HOME}/bin/sonar-scanner \
              -Dsonar.projectKey=PROYECTO-FINAL-QA \
              -Dsonar.projectName='PROYECTO FINAL QA' \
              -Dsonar.sources=app,config,resources,routes \
              -Dsonar.exclusions=vendor/**,storage/**,node_modules/** \
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
