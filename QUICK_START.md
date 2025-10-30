# Quick Start Guide

Get the warranty activation system running in 5 minutes!

## 🚀 Option 1: Quick Development Setup (Recommended)

### Step 1: Install Dependencies
```bash
npm install
```

### Step 2: Start Development Server
```bash
npm run dev
```

### Step 3: Open in Browser
```
http://localhost:3000
```

That's it! The application is now running with mock data.

---

## 🧪 Test the Application

### Test Data

**Working Contract IDs:**
- `IL-123456` (with installation)
- `D-789012` (without installation)

**Working Phone Numbers:**
- `+7 (999) 123-45-67`
- `+7 (999) 987-65-43`

### Quick Test Flow

1. **Navigate to** http://localhost:3000/garantia
2. **Enter** `IL-123456` in the phone/contract field
3. **Click** "Далее" (Next)
4. **Select** "Нет" for extra work
5. **Click** "Далее"
6. **Select** rating: 5 stars for sales
7. **Click** "Далее"
8. **Select** rating: 4 stars for delivery
9. **Click** "Далее"
10. **Select** rating: 5 stars for installation
11. **Click** "Далее"
12. **Select** any discounts you want
13. **Click** "Активировать гарантию"
14. **See** success page with warranty ID!

---

## 📱 Mobile Testing

### Using Browser DevTools
1. Open Chrome DevTools (F12)
2. Click device toolbar icon (Ctrl+Shift+M)
3. Select iPhone or Android device
4. Test the form on mobile viewport

### Using Real Device
1. Find your local IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
2. Make sure device is on same network
3. Open `http://YOUR_IP:3000` on mobile browser

---

## 🐛 Troubleshooting

### Port Already in Use?
```bash
# Kill process on port 3000
# Windows:
netstat -ano | findstr :3000
taskkill /PID <PID> /F

# Mac/Linux:
lsof -ti:3000 | xargs kill -9
```

### Dependencies Error?
```bash
# Clean install
rm -rf node_modules package-lock.json
npm install
```

### Build Errors?
```bash
# Clear Next.js cache
rm -rf .next
npm run dev
```

---

## 🔧 Configuration (Optional)

### Add Environment Variables

Create `.env.local`:
```env
# Optional: Analytics IDs
NEXT_PUBLIC_GA4_ID=your_ga4_id_here
NEXT_PUBLIC_YANDEX_METRIKA_ID=your_ym_id_here
```

The application will work without these; analytics will just log to console.

---

## 📊 What to Expect

### On First Load
- Form appears at `/garantia`
- Progress bar shows "Шаг 1 из 6"
- Phone/contract input field is focused

### During Form Fill
- Real-time validation
- Smooth step transitions
- Progress updates automatically
- Draft auto-saves to localStorage

### After Submission
- Loading spinner appears
- Redirects to success page
- Shows warranty ID (e.g., W-2025-000001)
- Displays discount reservation date

---

## 🎯 Key Features to Test

### 1. Form Validation
- ❌ Try empty phone - should show error
- ❌ Try invalid phone `123` - should show error
- ✅ Enter valid phone - error clears

### 2. Auto-formatting
- Type: `79991234567`
- Becomes: `+7 (999) 123-45-67`

### 3. Optional Steps
- Skip ratings - form still submits
- Only identification is required

### 4. Discount Logic
- Select multiple discounts - all are selected
- Click "Ничего не нужно" - others deselect
- Select another - "Ничего не нужно" deselects

### 5. Extra Work
- Click "Да" - fields appear
- Click "Добавить еще" - more fields
- Click trash icon - field removes

### 6. Navigation
- "Назад" button goes to previous step
- "Далее" button goes to next step
- Can't go next if validation fails

### 7. Installation Step
- With `IL-123456` - step 5 appears
- With `D-789012` - step 5 skips

---

## 🚀 Next Steps

### Want to customize?

**Colors:** Edit `tailwind.config.js`
```javascript
colors: {
  primary: {
    500: '#YOUR_COLOR',
    600: '#YOUR_DARKER_COLOR',
  }
}
```

**Text:** Edit component files in `/components/steps/`

**Validation:** Edit `lib/validation.ts`

**API Logic:** Edit `app/api/warranty/activate/route.ts`

---

## 📚 Documentation

- **README.md** - Full project documentation
- **IMPLEMENTATION.md** - Implementation details
- **docs/API.md** - API documentation
- **docs/DEPLOYMENT.md** - Production deployment

---

## ⚡ Performance Tips

### Development Mode
- Hot reload works automatically
- Changes reflect instantly
- Console shows helpful errors

### Production Build
```bash
npm run build
npm start
```
Runs in optimized production mode.

---

## 🎨 Customization Examples

### Change Main Color
```css
/* app/globals.css */
.bg-primary-600 {
  @apply bg-blue-600; /* Change to any color */
}
```

### Add New Discount
```typescript
// types/warranty.ts
export const DISCOUNT_OPTIONS = [
  // ... existing
  { id: 'new_discount_15', title: 'New Product', discount: 15 },
];
```

### Change Step Count
Simply hide/show steps in `components/WarrantyForm.tsx`

---

## 💡 Pro Tips

1. **Use Browser DevTools Console**
   - See analytics events
   - View API requests/responses
   - Check validation errors

2. **Test Error States**
   - Enter invalid data
   - Submit without required fields
   - Try same contract twice (409 error)

3. **Check Network Tab**
   - See API request payload
   - Verify response format
   - Monitor response time

4. **Mobile Testing**
   - Test all touch targets
   - Verify keyboard behavior
   - Check scroll performance

---

## ✅ Success Checklist

- [ ] npm install completed
- [ ] npm run dev running
- [ ] http://localhost:3000 loads
- [ ] Form appears correctly
- [ ] Can navigate through steps
- [ ] Form submits successfully
- [ ] Success page displays
- [ ] Mobile view works
- [ ] Validation works
- [ ] Analytics logs to console

---

## 🆘 Get Help

**Common Issues:**
1. Port in use → Change port: `PORT=3001 npm run dev`
2. Dependencies error → `rm -rf node_modules && npm install`
3. Build error → `rm -rf .next && npm run dev`
4. TypeScript error → Check Node.js version (need 18+)

**Still stuck?**
- Check `README.md` for detailed docs
- Review code comments in files
- Check browser console for errors

---

## 🎉 You're Ready!

The application is now running. Start testing the warranty activation flow!

**Happy coding! 🚀**
