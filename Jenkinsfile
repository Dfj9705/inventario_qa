pipeline {
  agent any

  stages {
    stage('Debug') {
      steps {
        echo "BRANCH_NAME = ${env.BRANCH_NAME}"
        script {
          if (isUnix()) {
            sh 'pwd && ls -la'
          } else {
            bat 'cd & dir'
          }
        }
      }
    }

    stage('Solo en QA') {
      when { branch 'qa' }
      steps {
        echo 'Este stage solo corre en la rama QA'
      }
    }
  }

  post {
    success { echo 'Pipeline ejecutó stages correctamente.' }
  }
}
