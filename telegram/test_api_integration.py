#!/usr/bin/env python3
"""
Test script to verify Telegram Bot -> Laravel API integration for fund transfers
"""

import asyncio
import sys
import os

# Add src to path
sys.path.append(os.path.join(os.path.dirname(__file__), 'src'))

from src.utils.api_client import api_client

async def test_fund_transfer():
    """Test the fund transfer API integration"""
    
    print("🧪 Testing Telegram Bot -> Laravel API Fund Transfer Integration")
    print("=" * 60)
    
    # Test data based on our API documentation
    test_transfers = [
        {
            'from_username': 'agent 007',
            'to_username': 'petya', 
            'amount': 100.00,
            'comment': 'Test transfer from Telegram Bot'
        },
        {
            'from_username': 'petya',
            'to_username': 'agent 001',
            'amount': 50.00,
            'comment': 'Another test transfer'
        }
    ]
    
    for i, transfer in enumerate(test_transfers, 1):
        print(f"\n📤 Test {i}: Transfer from {transfer['from_username']} to {transfer['to_username']}")
        print(f"💰 Amount: ${transfer['amount']:.2f}")
        print(f"📝 Comment: {transfer['comment']}")
        
        try:
            result = await api_client.submit_transfer(
                from_username=transfer['from_username'],
                to_username=transfer['to_username'],
                amount=transfer['amount'],
                comment=transfer['comment']
            )
            
            if result['success']:
                data = result.get('data', {})
                print(f"✅ SUCCESS!")
                print(f"   📄 Transfer ID: {data.get('fund_transfer_id', 'Unknown')}")
                print(f"   💰 Original Amount: ${data.get('original_amount', 0):.2f}")
                print(f"   💰 Final Amount: ${data.get('final_amount', 0):.2f}")
                
                if data.get('commission_applied'):
                    print(f"   📊 Commission: {data.get('commission_rate', 0)*100:.2f}% (${data.get('commission_amount', 0):.2f})")
                else:
                    print(f"   📊 Commission: No commission applied")
                    
            else:
                print(f"❌ FAILED: {result.get('error', 'Unknown error')}")
                print(f"   Status: {result.get('status', 'Unknown')}")
                
        except Exception as e:
            print(f"💥 EXCEPTION: {str(e)}")
    
    await api_client.close()
    print(f"\n🏁 Test completed!")

async def test_api_connectivity():
    """Test basic API connectivity"""
    
    print("\n🔌 Testing API Connectivity")
    print("-" * 30)
    
    try:
        # Try a simple request to test connectivity
        result = await api_client.submit_transfer(
            from_username='nonexistent_user',
            to_username='another_nonexistent_user',
            amount=1.00,
            comment='Connectivity test'
        )
        
        # We expect this to fail due to user not found, but it means API is reachable
        if not result['success'] and 'not found' in result.get('error', '').lower():
            print("✅ API is reachable (expected user not found error)")
        elif result['success']:
            print("✅ API is working perfectly!")
        else:
            print(f"⚠️  API reachable but unexpected error: {result.get('error')}")
            
    except Exception as e:
        print(f"❌ Cannot reach API: {str(e)}")
        print("\n🔧 Troubleshooting steps:")
        print("1. Make sure Laravel app is running on http://localhost:8000")
        print("2. Check that INTERNAL_API_KEY is set in acne-accounting/.env")
        print("3. Verify API endpoint in telegram/src/config/config.py")

if __name__ == "__main__":
    print("🚀 Starting API Integration Tests...")
    
    asyncio.run(test_api_connectivity())
    
    print("\n" + "="*60)
    print("💡 If connectivity test passes, you can run fund transfer tests:")
    print("   Uncomment the line below and run again")
    print("="*60)
    
    # Uncomment this line to run actual transfer tests:
    # asyncio.run(test_fund_transfer()) 