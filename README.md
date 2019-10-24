Blossom Backend Developer Test
==============================

In this test you are asked to build a simple application that handles a single file upload request with attached parameters.

Imagine you had a web form with the following input data:

- `file` - a file to be uploaded (a video)
- `upload` - multiple radio buttons which tell the application to what destination the file should be uploaded, possible values:
    - `dropbox`
    - `s3`
    - `ftp`
- `formats` - multiple checkboxes which tell the application to what formats the uploaded file should be converted/encoded, possible values:
    - `mp4`
    - `webm`
    - `ogv`

When a user comes to the page they can upload a file from their computer and send it to our application. Because we, as developers, care about scalability we don't want to store any files on our application servers so we send them somewhere to the cloud. We work with two cloud storage providers: Dropbox and Amazon S3 as well as we have an FTP server. Furthermore, the uploaded video file can be converted to a different format - `mp4`, `WebM` or `OGV` to make sure it plays nicely in web browsers.

When the user submits the form with `POST` HTTP method we need to handle all the data that has been sent. The request is already transformed to `\Symfony\Components\HttpFoundation\Request` object and we expect it to respond to the client with an instance of `\Symfony\Components\HttpFoundation\Response` class.

There is an existing test case that covers most of use cases which you can find in `tests/EncoderTest.php` and you can run it using `PHPUnit`.

Your job is to make sure that all of the tests in that file pass successfully.

# Notes

## Library mocks

We have created few mock libraries that you should use to handle all the hard tasks (uploading to the cloud, converting videos). They are located in `./stubs` directory.

They are essentially libraries that you would use when doing a real application - existing open source solutions of various code quality, different interfaces and different behavior. You should use them as much as possible.

Yes, your task comes down to wiring them up nicely and using them to handle the user requests.

## Tests

The existing tests can contain some errors. They were created by a code architect who didn't have existing code to test. If you find some bugs in them feel free to fix them.

# What you can do

- Write your code in `./src` directory.
- Edit `./composer.json` and install other dependencies
- Fix any errors in the tests themselves.

# What you cannot do

- Touch any code found in `./stubs` - they are vendor libraries from the outside world!

## What will we grade

- Code architecture
- Use of programming patterns
- Code style
- Clarity of the solution

## What we encourage you to do

- Write your own additional tests
- Document your code as much as possible
- Commit often so that we can follow your thinking process and see how you refactor your code
- Ask questions to us via email to clarify your task


Good luck!
developers@blossomeducational.com
