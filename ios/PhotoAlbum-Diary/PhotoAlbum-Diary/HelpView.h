//
//  ViewController.h
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "MyLib.h"

@interface HelpView : UIViewController<UIWebViewDelegate>
@property (weak, nonatomic) IBOutlet UIWebView *HelpWebView;

@end

