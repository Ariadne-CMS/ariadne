# How to contribute

Thanks for wanting to contribute. Here's a quick guide.

## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free)
* Submit a ticket for your issue, assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
  * We're using https://github.com/ariadne-cms/arc-base/issues to keep track of issues, you can too.
* Fork the repository on GitHub

## Making Changes

* ARC has its very own personality and way of doing things, make sure you understand
  how \arc\path and \arc\tree are used in all components and see if you can make use
  of them in your own changes as well
* Keep state out of the lowercase named classes, unless you're making a factory method,
  in which case use \arc\context as a dependency injection container.
* Keep it small and clean. ARC focuses on small code size and a small, simple and beautiful
  API. Don't cram in unneeded features.
* Don't extend other classes. Really. Unless you have tried composition ( proxying ) and 
  traits first. And then still don't.
* ARC conforms to [PSR-2][PSR2] for coding style, not because we love it, but because we need 
  a standard so we don't start fighting about it.
* Make sure you have added the necessary tests for your changes.
* Run _all_ the tests to assure nothing else was accidentally broken.


## Submitting Changes

* Commit your changes to your fork with complete, readable and english commit messages
* Push to your fork
* Submit a pull request
* Wait for us and hang out at #ariadne at ircnet, try http://webchat.xs4all.nl/ for a web interface

## Contributor License Agreement

By contributing your code to ARC you grant Muze BV. a non-exclusive, irrevocable, worldwide,
royalty-free, sublicenseable, transferable license under all of Your relevant intellectual property rights
(including copyright, patent, and any other rights), to use, copy, prepare derivative works of, distribute and
publicly perform and display the Contributions on any licensing terms, including without limitation:
(a) open source licenses like the MIT license; and (b) binary, proprietary, or commercial licenses. Except for the
licenses granted herein, You reserve all right, title, and interest in and to the Contribution.

You confirm that you are able to grant us these rights. You represent that You are legally entitled to grant the
above license. If Your employer has rights to intellectual property that You create, You represent that You have
received permission to make the Contributions on behalf of that employer, or that Your employer has waived such
rights for the Contributions.

You represent that the Contributions are Your original works of authorship, and to Your knowledge, no other person
claims, or has the right to claim, any right in any invention or patent related to the Contributions. You also
represent that You are not legally obligated, whether by entering into an agreement or otherwise, in any way that
conflicts with the terms of this license.

Muze BV. acknowledges that, except as explicitly described in this Agreement, any Contribution which
you provide is on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, EITHER EXPRESS OR IMPLIED,
INCLUDING, WITHOUT LIMITATION, ANY WARRANTIES OR CONDITIONS OF TITLE, NON-INFRINGEMENT, MERCHANTABILITY, OR FITNESS
FOR A PARTICULAR PURPOSE.


[PSR2]: http://www.php-fig.org/psr/psr-2/
