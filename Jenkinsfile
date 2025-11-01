pipeline {
  agent any
  options { skipDefaultCheckout(true) }
  triggers { githubPush() }
  environment {
    SONAR_SCANNER_HOME = tool 'testsonar'   // Global Tool
  }

  stages {
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
  }
}
