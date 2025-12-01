pipeline {
    agent any

    environment {
        DOCKERHUB_USER = 'SEU_DOCKERHUB_USER'
        DOCKERHUB_REPO = 'pdi-ecommerce'
        GIT_REPO = 'https://github.com/piercodegroup/pdi-ecommerce.git'
        GIT_BRANCH = 'main'
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: "${GIT_BRANCH}", url: "${GIT_REPO}"
            }
        }

        stage('Composer Install') {
            steps {
                sh 'composer install --no-interaction --prefer-dist'
            }
        }

        stage('Run Tests') {
            steps {
                sh './vendor/bin/phpunit --testdox || true'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh "docker build -t ${DOCKERHUB_USER}/${DOCKERHUB_REPO}:latest ."
            }
        }

        stage('DockerHub Login') {
            steps {
                withCredentials([string(credentialsId: 'dockerhub-pass', variable: 'DOCKER_PASS')]) {
                    sh 'echo $DOCKER_PASS | docker login -u ${DOCKERHUB_USER} --password-stdin'
                }
            }
        }

        stage('Push Image') {
            steps {
                sh "docker push ${DOCKERHUB_USER}/${DOCKERHUB_REPO}:latest"
            }
        }

        stage('Deploy (docker-compose)') {
            steps {
                sh 'docker compose down || true'
                sh 'docker compose pull || true'
                sh 'docker compose up -d --build'
            }
        }
    }

    post {
        success {
            echo "Pipeline finalizado com sucesso."
        }
        failure {
            echo "Pipeline falhou. Verifique logs."
        }
    }
}
