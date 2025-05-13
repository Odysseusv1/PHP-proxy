# WebVoyager Proxy

A PHP-based web proxy that allows users to browse any website through an intermediary service. This proxy can be deployed on Vercel or other hosting platforms.

## Features

- Modern, clean interface with responsive design
- Anonymous browsing of websites through the proxy
- Support for various content types (HTML, CSS, JavaScript, images, etc.)
- URL rewriting to maintain browsing through the proxy
- Quick access links to popular websites
- Mobile-friendly design

## Deploying to Vercel

This proxy includes configuration for deployment on Vercel. Follow these steps to deploy:

1. Create a Vercel account if you don't have one already
2. Install the Vercel CLI: `npm i -g vercel`
3. Clone this repository to your local machine
4. Navigate to the project directory
5. Run `vercel login` to authenticate with your Vercel account
6. Run `vercel` to deploy the project
7. Follow the prompts to complete the deployment

The deployment will use the configuration in `vercel.json` to set up the PHP runtime and route requests appropriately.

## How It Works

1. The user enters a URL in the proxy form
2. The proxy server fetches the content from the target website
3. The proxy processes the content to rewrite links and resources
4. The processed content is sent to the user's browser
5. All subsequent requests are also processed through the proxy

## Local Development

To run this project locally:

```bash
php -S localhost:5000
```

Then visit http://localhost:5000 in your browser.

## Credits

WebVoyager Proxy © 2025