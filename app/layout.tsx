import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'Активация гарантийного талона | I-Laminat',
  description: 'Активируйте гарантийный талон и получите специальные скидки на сопутствующие товары',
  robots: 'noindex, nofollow',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="ru">
      <head>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
      </head>
      <body className="antialiased">{children}</body>
    </html>
  );
}
