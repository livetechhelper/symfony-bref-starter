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
git clone ... YOUR_DIR
cd YOUR_DIR

# I use docker so I don't have to mess around with PHP versions, etc. but you can
# skip this part and do these locally if you want to but it also includes the DB...
docker-compose up -d
# go into the container
docker exec -ti symfony_bref_starter_dev_php bash
# navigate to the app dir
cd /var/task

# now actually initialize the project / install dependencies, etc.
composer install
yarn install
yarn dev
```
The above just creates a new project in your desired directory with the basic project structure and installs the required packages.

#### View the Project
If everything has worked so far, you should be able to see the project locally here:
[http://localhost:8011/](http://localhost:8011/)

You should see "Hello there!" 

*Note: You can also use `symfony:server:start` as well, especially if you need SSL locally but you will have to change the DB to point to the right host (outside of docker).*

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
10. Copy the ARN for each and replace both the domain names and certs in your project's serverless.yaml 

### Deploy your application
This assumes that you have logged into the AWS cli and you have an authorized session in your terminal, if you do not, you will not be able to deploy. If you have followed the steps so far, you should do this inside the docker container which should have your access key id and secret already set.

```shell
# start with dev
serverless delpoy --stage=dev
```

## Additional (Optional) Features
There are other features included by default in the project, you do not have to use them but I found them to be pretty common and sometimes complex to setup, so I tried to include what I think is a good starter and included my most common building blocks.

### Sessions and Data Cache
I had some difficulties in setting this up for many reasons, so I put it in here so this pain could be avoided. You do not have to use this and can remove the config for this.

In this setup, I use some custom things because I do not want the sessions table to be the same as the data cache table so I have overriden things in a (probably not so correct way), any improvements welcome, but if you just want something working out of the box, it does work or will at least point you in the right direction.

It uses DynamoDB for this as there is on demand pricing and you don't need to pay for instances so it helps keep costs down.

### Symfony Messenger (Using AWS SQS)
I have included an example, I normally use this for emails, or interacting with 3rd party APIs. The test doesn't really do anything but you can see inside AWS (or bref dashboard if using) the throughput, and also failures that end up in the Dead Letter Queue.

You can simply add a new Message class e.g.:
```shell
src/Message/SomethingMessage
```
If you wish for this to be asychronously handled using SQS, then you will need to implement the `AsyncMessageInterface`.

You can then add the message handler e.g.:
```shell
src/MessageHandler/SomethingMessageHandler
```

### Creating Your Database(s) [TODO]
While you do not have to use a database of course, this project does have this configured, however you will need to manually create your database in AWS, but the permissions are configured in the serverless.yaml to ensure your application can access it.

*Note: This is using AWS RDS and not Aurora, so it's just an example to get you started.*

1. Navigate to the RDS section in your AWS console
2. Setup the DB
3. ... @TODO - actually complete this section / guide...