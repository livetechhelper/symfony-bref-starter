# Based from Symfony Starter for Bref
Looking to start a new symfony project and want things like:
 - Deploy to AWS lambda (zero to little infrastructure + huge scalability)
 - Learning how to use [bref](https://bref.sh) (serverless for PHP)
 - Asynchronous message handling (using AWS SQS or other providers)

### Initialize the project
```shell
git clone https://github.com/uvservices/video-request-site.git YOUR_DIR
cd YOUR_DIR

# to start the project just run the startup script (this will run composer install, etc. if it was never ran)
./startup.sh
```

To interact with the code, run console commands, etc. you can just go into the docker as shown below 
```shell
# I use docker so I don't have to mess around with PHP/node/composer versions, etc. but you can
# skip this part and do these locally if you want to but it also includes the DB...
docker-compose up -d
# go into the container
docker exec -ti video_request_site_dev_php bash
# navigate to the app dir
cd /var/task
```
The above just creates a new project in your desired directory with the basic project structure and installs the required packages.

#### View the Project
If everything has worked so far, you should be able to see the project locally here:
[http://localhost:8015/](http://localhost:8015/)

You should see "Hello there!" 

*Note: You can also use `symfony:server:start` because it handles SSL locally but you will have to change the DB to point to the right host (outside of docker).*

#### Set AWS config in .env.local
Create a new `.env.local` file in the root of your project and add the details of the key you created in the previous steps:
```shell
APP_ENV=local
AWS_ACCESS_KEY_ID=REPLACE_WITH_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY=REPLACE_WITH_ACCESS_SECRET_KEY
```
This will be passed through to your docker image so you don't need to install and setup everything on your machine but can do everything from inside the docker containers.

### Deploy your application

First you need to login to AWS console using your access key and secret:
```shell
aws configure
# you will be prompted for your key and secret
```

This assumes that you have logged into the AWS cli and you have an authorized session in your terminal, if you do not, you will not be able to deploy. If you have followed the steps so far, you should do this inside the docker container which should have your access key id and secret already set.
```shell
# start with dev
serverless deploy --stage=dev
```

For production, you will need to build the application in prod first (see the [.gitlab-ci.yml](.gitlab-ci.yml)) for examples of how. Then run:
```shell
# deploy prod
serverless deploy --stage=prod
```

## Additional (Optional) Features
There are other features included by default in the project, you do not have to use them but I found them to be pretty common and sometimes complex to setup, so I tried to include what I think is a good starter and included my most common building blocks.

### Secrets Storage / Database Configuration
This project can use AWS systems manager parameter store to store secrets, and that is where you should put things like DB creds, API creds, secrets, etc.
To do this, follow these steps:
1. Navigate to the <a href="https://us-east-1.console.aws.amazon.com/systems-manager/parameters/">parameter store section</a> in your AWS console and
2. Click "Create Parameter" and add in your parameter. E.g. `/symfony-bref-starter-dev/DATABASE_URL`
   Note: you can also do this in the console e.g.
    ```shell
      aws ssm put-parameter --name "/symfony-bref-starter-dev/DATABASE_URL" --value "mysql://username:password@host:port/dbname" --type "String"
    ```
3. Now you can reference any of these params in your serverless.yml file by using the following syntax:
   ```yaml
   params:
      dev:
        DATABASE_URL: ${ssm:/symfony-bref-starter-dev/DATABASE_URL, ''}
      
      prod:
        DATABASE_URL: ${ssm:/symfony-bref-starter-prod/DATABASE_URL, ''}
   
   provider:
      environment:
        DATABASE_URL: ${param:DATABASE_URL}
   ```
   
### Running Console Commands
You can run any console commands as you normally do but you have to run through bref cli as shown below:
```shell
# as you can see below, you just enter the command into the args.
# Note: You do NOT need the normal `bin/console` prefix.
serverless bref:cli --args="doctrine:schema:update --dump-sql"
```
   
### Testing the Database
I have included a test entity and fixtures so you can ensure your application can connect to the DB as expected. You simply need to create the table and load the fixtures as shown below.
NB: run these commands from inside the docker because any `serverless` command you run needs to have the AWS credentials loaded.
```shell
# this will create the "widget" table in your DB
serverless bref:cli --args="doctrine:schema:update --force"
# note: this will WIPE the connected DB if there is anything in it
# (although should be empty at this point if ran at the beginning)
serverless bref:cli --args="doctrine:fixtures:load --no-interaction"
```

Now you can click the link on the homepage to test the DB, if it works it should display 2 "widget" entries, this means everything is working as expected.

### Sessions and Data Cache
I had some difficulties in setting this up for many reasons, so I put it in here so this pain could be avoided. You do not have to use this and can remove the config for this.

In this setup, I use some custom things because I do not want the sessions table to be the same as the data cache table so I have overridden things in a (probably not so correct way), any improvements welcome, but if you just want something working out of the box, it does work or will at least point you in the right direction.

It uses DynamoDB for this as there is on demand pricing and you don't need to pay for instances so it helps keep costs down.

### Symfony Messenger (Using AWS SQS)
I have included an example, I normally use this for emails, or interacting with 3rd party APIs. The test doesn't really do anything but you can see inside AWS (or bref dashboard if using) the throughput, and also failures that end up in the Dead Letter Queue.

You can simply add a new Message class e.g.:
```shell
src/Message/SomethingMessage
```
If you wish for this to be asynchronously handled using SQS, then you will need to implement the `AsyncMessageInterface`.

You can then add the message handler e.g.:
```shell
src/MessageHandler/SomethingMessageHandler
```

### DELETING the deployments
If you no longer want to use the application and want to remove the stack from AWS, you can simply run
```shell
# remove dev
serverless remove --stage=dev

# remove prod
serverless remove --stage=prod
```

This will fail as you cannot delete a non-empty bucket in S3, so you have to go and find the bucket, empty it and then re-run. If it still fails, you can try and delete the bucket itself then re-run. If all else fails, find the cloudformation stack in AWS and delete it from there.

*Note, this will NOT remove any certs or domains you have configured inside AWS, you will have to do those manually if you want to remove them. Also it WILL remove the DB, so make sure that's OK.*
