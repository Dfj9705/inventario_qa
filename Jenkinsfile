pipeline {
  agent any
  options { timestamps() }

  stages {
    stage('Debug') {
      steps {
        echo "Rama detectada: ${env.BRANCH_NAME}"
        sh 'php -v || echo "PHP no instalado en el agente"'
      }
    }

    stage('Tests QA') {
      when { branch 'qa' }
      steps {
        echo "Ejecutando tests para QA..."
        sh '''
          php -r "echo 'Pruebas simuladas ejecutadas';"
          # composer install --no-progress --prefer-dist
          # php artisan test
        '''
      }
    }
  }

  post {
    success { echo 'Pipeline completado correctamente' }
    failure { echo 'Falló el pipeline' }
  }
}
