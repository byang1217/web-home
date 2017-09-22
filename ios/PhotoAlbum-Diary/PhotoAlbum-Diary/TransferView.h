//
//  ViewController.h
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "AlbumSync.h"

@interface TransferView : UITableViewController <AlbumSyncDelegate>
-(void)statusUpdateHandler:(NSDictionary *)statusInfDict;

@end
