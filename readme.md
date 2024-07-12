<a href="https://livetechhelper.com/repos/livetechhelper/symfony-bref-starter" target="_blank"><img src="https://img.shields.io/badge/Get_live_support-livetechhelper%2Fsymfony--bref--starter-blue" alt="Get help with livetechhelper/symfony-bref-starter" /></a>

# Symfony Starter for Bref
Looking to start a new symfony project and want things like:
 - Deploy to AWS lambda (zero to little infrastructure + huge scalability)
 - Learning how to use [bref](https://bref.sh) (serverless for PHP)
 - Asynchronous message handling (using AWS SQS or other providers)

This project is aimed at you! Get started and don't worry about deployments for your symfony projects. Easy dev/prod setup and message queue handling. 


## Why Bref?
[bref](https://bref.sh) is a fantastic and well tested way of running PHP applications via serverless, handling easy deployments and environments on AWS. Projects using bref (as of March 2024) serve over 30 BILLION requests per month! 
Thanks to [Matthieu Napoli](https://github.com/mnapoli) for all of his work over the years to make it so easy and for the tools he's added to the stack to make things that much easier like:
 - [Bref Dashboard](https://dashboard.bref.sh/) - instead of struggling with trying to setup your own in AWS, this automatically recognizes your applications and lets you view performance, logs, etc. in a standalone desktop app
 - [7777](https://port7777.com/) - Handles remote access to your private DB instances on AWS. This is a huge timesaver and lets you keep your DBs where they should be (not public)
 - [Serverless Visually Explained](https://serverless-visually-explained.com/) - For those that are not too familiar with serverless, or just want a refresher, this course it fantastic and explains everything VERY well with great visual aids

## Getting Started
If you have an existing Symfony (or other PHP framework / standalone application), you can view the documentation on [bref.sh](https://bref.sh) for guides of how to do this. 

This project is designed to be a starting point for developers creating a new project and want to start with all of the common use cases covered without specializing too much. This is based off of the symfony starter project and I have just added some basic setup and configuration to get you started.

### Initialize the project
```shell
git clone https://github.com/livetechhelper/symfony-bref-starter.git YOUR_DIR
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
docker exec -ti symfony_bref_starter_dev_php bash
# navigate to the app dir
cd /var/task
```
The above just creates a new project in your desired directory with the basic project structure and installs the required packages.

#### View the Project
If everything has worked so far, you should be able to see the project locally here:
[http://localhost:8011/](http://localhost:8011/)

You should see "Hello there!" 

*Note: You can also use `symfony:server:start` because it handles SSL locally but you will have to change the DB to point to the right host (outside of docker).*

### AWS Setup
While things are mostly automated, there are still the following requirements:
 - An AWS account with billing enabled / setup
 - A configured domain in Route53 (we will cover how to do this below)
 - Valid AWS credentials (key and secret - we will also cover this)

#### AWS Account Setup
As this is already documented on the bref project, you can follow the (better) documentation here to setup the basics: [Bref Setup](https://bref.sh/docs/setup).

I have included an example (working) IAM role JSON as I needed to make some changes: [IAM ROLE CONFIG](docs/configs/iam_role.md)

#### Set AWS config in .env.local
Create a new `.env.local` file in the root of your project and add the details of the key you created in the previous steps:
```shell
APP_ENV=local
AWS_ACCESS_KEY_ID=REPLACE_WITH_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY=REPLACE_WITH_ACCESS_SECRET_KEY
```
This will be passed through to your docker image so you don't need to install and setup everything on your machine but can do everything from inside the docker containers.

#### Domain Setup (AWS only for now - other providers not included in docs yet...)
*Note: This is not a requirement, if you just want to see, you can get rid of the domain and view from the cloudfront URL you see when you run `serverless deploy`*

Once you have the above setup (i.e. serveless installed, AWS account configured, etc.), you can setup your domain in Route53 using the steps below.

1. Navigate to Route53 in your AWS console.
2. Follow the instructions to create a new "Hosted Zone" for your new domain (ensure it's a public hosted zone)
3. Once it's setup, navigate to your domain provider and update the DNS records to point DNS to AWS
4. Now we need to setup certs, we will do manually, but this can be done in serverless configs (see [Tiago Boeing's Guide](https://tiagoboeing.medium.com/serverless-framework-aws-automatically-creating-certificate-and-domain-for-your-app-98cd5e31b66c))
5. Navigate to AWS Certificate Manager in AWS console.
6. Request a certificate (ensure it's a public SSL/TLS certificate by Amazon).
7. We will have 1 cert for the dev environment and one for the prod env (you can skip dev if you don't want it)
8. For each domain, view the requested cert, it will be "pending validation" for a while
9. You will see the option for "Create records in Route53", click this to have AWS auto add the CNAME records so you don't have to
10. Create AAAA records for both dev and prod, you will get the option to link to an AWS resource, choose the corresponding cloudfront distribution
11. Copy the ARN for each and replace both the domain names and certs in your project's serverless.yaml

*Note: Sometimes even if everything in AWS says it's synced or up to date, it can take a while to actually answer for the domain / for DNS to propogate*

### Using a Database
While you do not have to use a database of course, this project does have this configured. The permissions are also configured in the serverless.yaml to ensure your application can access it.

*Note: This is using AWS RDS and not Aurora, so it's just an example to get you started.*

If you do NOT wish to use a database in your project, please comment out, or remove the DB and VPC related lines in serverless.yml before deploying. This will save costs as there is no VPC needed if you're not using a DB. Not using a DB means it's basically free up to 1M invocations per month.

**Note: You should not be using master user, etc. so you should setup a new MySQL user in the AWS RDS admin and use this one and put those credentials in the parameter store (explained below)**


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
