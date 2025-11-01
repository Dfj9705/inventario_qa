pipeline {
  agent any
  options { timestamps(); ansiColor('xterm') }

  environment {
    COMPOSER_NO_INTERACTION = '1'
    APP_ENV = 'testing'
    XDEBUG_MODE = 'coverage'
    SONAR_SCANNER_HOME = tool 'testsonarjenkins'     // Nombre del tool en Jenkins
  }

  triggers { githubPush() } // el webhook de GitHub dispara el build

  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Setup PHP deps') {
      when { branch 'qa' }
      steps {
        sh '''
          php -v
          composer -V || curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
          composer install --no-progress --prefer-dist
        '''
      }
    }

    stage('Prep Laravel (testing)') {
      when { branch 'qa' }
      steps {
        sh '''
          cp -f .env.testing .env || true
          php -r "file_exists('.env') || copy('.env.example', '.env');"
          php artisan key:generate
          php artisan config:clear
          php artisan cache:clear
          php artisan migrate --database=testing --force || true
        '''
      }
    }

    stage('Tests + Coverage') {
      when { branch 'qa' }
      steps {
        sh '''
          mkdir -p storage/coverage
          # Usa phpunit directamente para asegurar cobertura:
          ./vendor/bin/phpunit --testdox --coverage-clover storage/coverage/coverage.xml
        '''
      }
      post {
        always {
          junit allowEmptyResults: true, testResults: 'tests/**/junit*.xml, junit.xml'
          publishHTML(target: [
            reportName: 'Coverage',
            reportDir: 'storage/coverage',
            reportFiles: 'index.html',
            keepAll: true,
            alwaysLinkToLastBuild: true,
            allowMissing: true
          ])
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
            if (qg.status != 'OK') {
              error "Quality Gate FAILED: ${qg.status}"
            }
          }
        }
      }
    }

    stage('Auto-PR a main (opcional)') {
      when { branch 'qa' }
      steps {
        // Crea o actualiza PR qa -> main si todo pasó
        sh '''
          echo "Crear PR de qa -> main (usa GitHub CLI o API)"
        '''
      }
    }
  }

  post {
    success {
      echo 'Build OK en qa. El estado en GitHub permitirá el merge a main.'
    }
    failure {
      echo 'Build falló. No se debe mergear a main.'
    }
  }
}
