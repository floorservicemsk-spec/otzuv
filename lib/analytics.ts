// Analytics helper for GA4 and Yandex Metrika
export const trackEvent = (eventName: string, params?: Record<string, any>) => {
  // Google Analytics 4
  if (typeof window !== 'undefined' && (window as any).gtag) {
    (window as any).gtag('event', eventName, params);
  }
  
  // Yandex Metrika
  if (typeof window !== 'undefined' && (window as any).ym) {
    (window as any).ym(0, 'reachGoal', eventName, params);
  }
  
  console.log('Analytics event:', eventName, params);
};

export const analyticsEvents = {
  warrantyStart: () => trackEvent('warranty_start'),
  
  stepCompleted: (step: number) => trackEvent('id_step_completed', { step }),
  
  warrantySubmit: (data: any) => trackEvent('warranty_submit', {
    has_ratings: !!(data.sales_rate || data.delivery_rate || data.installation_rate),
    has_extra_work: data.has_extra_work,
    discounts_count: data.discounts.length,
  }),
  
  warrantyActivated: (warrantyId: string) => trackEvent('warranty_activated', {
    warranty_id: warrantyId,
  }),
  
  discountSelected: (discountId: string) => trackEvent('discount_selected', {
    discount: discountId,
  }),
  
  reviewLinkClicked: (source: string) => trackEvent('review_link_clicked', {
    source,
  }),
};
