pipeline {
  agent {
    docker {
      image 'php:8.2-cli'
      args '-u root'
    }
  }
  options { skipDefaultCheckout(true) }
  triggers { githubPush() }
  environment {
    COMPOSER_NO_INTERACTION = '1'
    XDEBUG_MODE = 'coverage'
    SONAR_SCANNER_HOME = tool 'testsonar'   // Global Tool
  }

  stages {
    stage('Checkout') {
      steps { checkout scm }
    }

    stage('Install deps') {
      when { branch 'qa' }
      steps {
        sh '''
          which composer || (curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer)
          pecl install xdebug || true
          docker-php-ext-enable xdebug || true
          composer install --no-progress --prefer-dist
        '''
      }
    }

    stage('Prepare testing env') {
      when { branch 'qa' }
      steps {
        sh '''
          cp -f .env.testing .env || true
          php -r "file_exists('.env') || copy('.env.example','.env');"
          php artisan key:generate || true
          # DB de pruebas simple: sqlite en archivo (opcional)
          mkdir -p database && touch database/testing.sqlite || true
          php artisan config:clear || true
          php artisan migrate --force || true
        '''
      }
    }

    stage('Run tests + coverage') {
      when { branch 'qa' }
      steps {
        sh '''
          mkdir -p storage/coverage
          ./vendor/bin/phpunit --coverage-clover storage/coverage/coverage.xml
        '''
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
}
