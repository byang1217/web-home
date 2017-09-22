//
//  ScanerVC.h
//  SuperScanner
//
//  Created by Jeans Huang on 10/19/15.
//  Copyright © 2015 gzhu. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "MyLib.h"

@interface ScanerVC : UIViewController
@property (copy) BOOL (^scanResultHandler)(NSString *str, NSString **errString);
@end
